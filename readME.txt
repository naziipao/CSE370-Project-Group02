Welcome to this project!


================
Prerequisites: |
================

1) Node.js
2) XAMPP


================
Setup Process: |
================

	1. Database Setup:

    		--> Open the XAMPP Control Panel and start both the Apache and MySQL modules.

    		--> Open your web browser and navigate to http://localhost/phpmyadmin.

    		--> Create a brand new, empty database named smart_recycling.

    		--> Click on your newly created database, navigate to the Import tab at the top of the screen, and upload the project's single exported .sql file.

   		--> Click Go. Your database tables and columns will instantly rebuild themselves.


	2. Application Setup

    		--> Open your terminal (or Command Prompt/PowerShell).

    		--> Navigate to the root folder of this project (where the server.js file is located).

    		--> Run the following command to download the required Node.js dependencies (Express and MySQL2): npm install

	3. Running the Application

    		--> Keep your terminal open in the project's root directory.

    		--> Start the backend server by typing:  node server.js
		--> You should see two success messages in your terminal indicating that the server is running on port 3000 and the database has connected successfully.


=======================
Accessing the Portal: |
=======================

		--> Open your web browser and go to http://localhost:3000 if your terminal shows this same port id otherwise change the port id according to the one that you set.