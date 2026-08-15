function switchForm(targetForm) {
  const loginForm = document.getElementById('loginForm');
  const signupForm = document.getElementById('signupForm');

  if (targetForm === 'signup') {
    loginForm.classList.remove('active');
    signupForm.classList.add('active');
  } else {
    signupForm.classList.remove('active');
    loginForm.classList.add('active');
  }
}