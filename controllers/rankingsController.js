// File: controllers/rankingsController.js
const db = require('../config/db');

// Same promise wrapper used in your other controllers.
const executeQuery = (query, params = []) => {
  return new Promise((resolve, reject) => {
    db.query(query, params, (err, results) => {
      if (err) return reject(err);
      resolve(results);
    });
  });
};

/*
  ONE query = the single source of truth for institute ranking.

  Baseline points  -> edu_institute_stats.CumulativeEarnedPoints
  Live points      -> SUM of every deposit made by that institute's students
  Members          -> how many students are linked to that institute

  Nothing is stored as "rank". The order is recalculated on every request,
  so the leaderboard reflects the database as it is right now.
*/
const LEADERBOARD_QUERY = `
  SELECT
    e.institute_EIIN,
    e.Institute_name,
    COALESCE(e.CumulativeEarnedPoints, 0) + COALESCE(SUM(d.earned_points), 0) AS total_points,
    COUNT(DISTINCT s.User_id) AS member_count
  FROM edu_institute_stats e
  LEFT JOIN student s ON s.institute_EIIN = e.institute_EIIN
  LEFT JOIN deposit d ON d.User_id = s.User_id
  GROUP BY e.institute_EIIN, e.Institute_name, e.CumulativeEarnedPoints
  ORDER BY total_points DESC, e.Institute_name ASC
`;

/*
  Runs the query and attaches a rank number to each row.

  Ranking rule (standard competition ranking, same as sports tables):
  equal points share the same rank, and the next rank skips.
  1000, 1000, 900  ->  1, 1, 3

  Exported so the dashboard can reuse it instead of duplicating the logic.
*/
const getLeaderboard = async () => {
  const rows = await executeQuery(LEADERBOARD_QUERY);

  let currentRank = 0;
  let previousPoints = null;

  return rows.map((row, index) => {
    const points = Number(row.total_points) || 0;

    if (points !== previousPoints) {
      currentRank = index + 1;
      previousPoints = points;
    }

    return {
      rank: currentRank,
      eiin: row.institute_EIIN,
      name: row.Institute_name || 'Unknown Institute',
      points: points,
      members: Number(row.member_count) || 0
    };
  });
};

// GET /api/rankings
const getRankings = async (req, res) => {
  try {
    const userId = req.session?.userId;
    const rankings = await getLeaderboard();

    // Find the logged-in user's own institute so we can highlight it.
    let myInstitute = null;
    const studentRows = await executeQuery(
      'SELECT institute_EIIN FROM student WHERE User_id = ?',
      [userId]
    );

    if (studentRows.length > 0 && studentRows[0].institute_EIIN) {
      const myEiin = studentRows[0].institute_EIIN;
      myInstitute = rankings.find(item => item.eiin === myEiin) || null;
    }

    return res.status(200).json({
      success: true,
      totalInstitutes: rankings.length,
      myInstitute: myInstitute,   // null for non-students
      rankings: rankings
    });

  } catch (error) {
    console.error('RANKINGS ERROR:', error);
    return res.status(500).json({
      success: false,
      message: 'Database Error: ' + (error.sqlMessage || error.message)
    });
  }
};

module.exports = { getRankings, getLeaderboard };
