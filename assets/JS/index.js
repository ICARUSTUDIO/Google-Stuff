// Select the settings icon and menu
const settingsIcon = document.getElementById('settings-icon');
const settingsMenu = document.getElementById('settings-menu');
const closeBtn = document.getElementById('close-btn');
const resetUsernameDiv = document.getElementById('reset-username');
const resetUsernamePage = document.getElementById('reset-usernamePage');
const resetPwdDiv = document.getElementById('reset-pwd');
const resetPwdPage = document.getElementById('reset-pwd-page');
const generateLinksDiv = document.getElementById('generate-links');
const generateLinksPage = document.getElementById('generate-links-page')
const backBtn = document.getElementById('back-btn');
const themeToggle = document.querySelector('.switch input[type="checkbox"]');
const body = document.body;
const filterBtn = document.getElementById('filter-btn');
const filterPane = document.getElementById('filter-pane');
const fromDateInput = document.getElementById('from-date');
const toDateInput = document.getElementById('to-date');
const themeDiv = document.getElementById('theme');


// Add event listener to settings icon
settingsIcon.addEventListener('click', () => {
  // Toggle the 'show' class on settings menu
  settingsMenu.classList.toggle('show');
});

// Add event listener to close button
closeBtn.addEventListener('click', (e) => {
  // Prevent event bubbling
  e.stopPropagation();
  // Remove the 'show' class from settings menu
  settingsMenu.classList.remove('show');
});

function stopEventPropagation(event) {
    event.stopPropagation();
}
settingsMenu.addEventListener('click', stopEventPropagation);



// Function to display reset username page
function displayResetUsernamePage() {
    resetUsernameDiv.style.display = 'none';
    resetPwdDiv.style.display = 'none';
    generateLinksDiv.style.display= 'none';
    resetUsernamePage.style.display = 'block';
    closeBtn.style.display = 'none';
    backBtn.style.display = 'block';
    themeDiv.style.display = 'none';

  }
  
  // Add event listener to reset username div
  resetUsernameDiv.addEventListener('click', displayResetUsernamePage);
  
  // Function to display reset password page
  function displayResetPwdPage() {
    resetUsernameDiv.style.display = 'none';
    resetPwdDiv.style.display = 'none';
    generateLinksDiv.style.display= 'none';
    resetPwdPage.style.display = 'block';
    closeBtn.style.display = 'none';
    backBtn.style.display = 'block';
    themeDiv.style.display = 'none';
  }
  
  // Add event listener to reset password div
  resetPwdDiv.addEventListener('click', displayResetPwdPage);
  
  // Function to display the generate links page
  function displayGenerateLinksPage() {
    resetUsernameDiv.style.display = 'none';
    resetPwdDiv.style.display = 'none';
    generateLinksDiv.style.display= 'none';
    generateLinksPage.style.display = 'block';
    closeBtn.style.display = 'none';
    backBtn.style.display = 'block';
    themeDiv.style.display = 'none';
  }
  
  // Add event listener to generate links div
  generateLinksDiv.addEventListener('click', displayGenerateLinksPage);

  // Function to go back to settings menu
  function goBackToSettingsMenu() {
    resetUsernameDiv.style.display = 'flex';
    resetPwdDiv.style.display = 'flex';
    generateLinksDiv.style.display ='flex';
    themeDiv.style.display = 'flex';
    resetUsernamePage.style.display = 'none';
    resetPwdPage.style.display = 'none';
    generateLinksPage.style.display ='none';
    closeBtn.style.display = 'block';
    backBtn.style.display = 'none';
  }
  
  // Add event listener to back button
  backBtn.addEventListener('click', goBackToSettingsMenu);


// Function to toggle theme
function toggleTheme() {
    // Check if dark theme is selected
    if (themeToggle.checked) {
      // Add dark theme class to body
      body.classList.add('dark-theme');
      // Save theme preference in local storage
      localStorage.setItem('theme', 'dark');
    } else {
      // Remove dark theme class from body
      body.classList.remove('dark-theme');
      // Save theme preference in local storage
      localStorage.setItem('theme', 'light');
    }
  }

// Add event listener to theme toggle switch
themeToggle.addEventListener('change', toggleTheme);

// Initialize theme based on stored preference
const storedTheme = localStorage.getItem('theme');
if (storedTheme === 'dark') {
  themeToggle.checked = true;
  body.classList.add('dark-theme');
} else {
  themeToggle.checked = false;
  body.classList.remove('dark-theme');
}


// Select the filter button and pane


// Function to toggle filter pane visibility
function toggleFilterPane() {
  filterPane.classList.toggle('show');
}

// Add event listener to filter button
filterBtn.addEventListener('click', toggleFilterPane);

// Function to enable/disable to date input
function enableToDateInput() {
  if (fromDateInput.value === '') {
    toDateInput.setAttribute('disabled', true);
    toDateInput.value = '';
  } else {
    toDateInput.removeAttribute('disabled');
  }
}

// Add event listener to from date input
fromDateInput.addEventListener('input', enableToDateInput);

// Initialize to date input as disabled
toDateInput.setAttribute('disabled', true);

// Function to set minimum date for to date input
function setMinDateForToDateInput() {
  if (fromDateInput.value !== '') {
    const minDate = new Date(fromDateInput.value);
    minDate.setDate(minDate.getDate() + 1);
    toDateInput.min = minDate.toISOString().split('T')[0];
  } else {
    toDateInput.min = '';
  }
}

// Add event listener to from date input
fromDateInput.addEventListener('input', setMinDateForToDateInput);


const inputField = document.getElementById('generate-links-num');

inputField.addEventListener('keypress', (e) => {
  const inputValue = parseInt(inputField.value + e.key);
  
  if (isNaN(inputValue) || inputValue < 1 || inputValue > 100) {
    e.preventDefault();
  }
});

inputField.addEventListener('paste', (e) => {
  e.preventDefault();
  const pasteData = e.clipboardData.getData('Text');
  const inputValue = parseInt(pasteData);
  
  if (!isNaN(inputValue) && inputValue >= 1 && inputValue <= 100) {
    inputField.value = inputValue;
  }
});