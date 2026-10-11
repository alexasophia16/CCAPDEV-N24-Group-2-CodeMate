const tutorsFile = 'mock-tutors.json';
const storageKey = 'codemate_new_bookings';
const tutorId = Number(new URLSearchParams(window.location.search).get('id'));

const form = document.getElementById('booking-form');
const message = document.getElementById('booking-message');
const subjectSelect = document.getElementById('subject');
const datetimeInput = document.getElementById('datetime');
const modeSelect = document.getElementById('mode');
const cancelButton = document.getElementById('cancel-button');

const now = new Date();
now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
datetimeInput.min = now.toISOString().slice(0, 16);

function showMessage(text, linkText, linkHref) {
  message.textContent = text + ' ';
  if (linkText) {
    const link = document.createElement('a');
    link.textContent = linkText;
    link.href = linkHref;
    message.appendChild(link);
  }
}

fetch(tutorsFile)
  .then(response => response.json())
  .then(tutors => {
    const tutor = tutors.find(item => item.id === tutorId);

    if (!tutor) {
      showMessage('No tutor selected.', 'Find a tutor', 'find-tutor.html');
      return;
    }

    if (!tutor.verified) {
      showMessage(tutor.name + ' is not verified yet, so bookings are not open.', 'Back to profile', 'tutor-profile.html?id=' + tutor.id);
      return;
    }

    document.getElementById('tutor-name').textContent = 'with ' + tutor.name;

    tutor.subjects.forEach(subject => {
      const option = document.createElement('option');
      option.value = subject;
      option.textContent = subject;
      subjectSelect.appendChild(option);
    });

    cancelButton.addEventListener('click', () => {
      window.location.href = 'tutor-profile.html?id=' + tutor.id;
    });

    form.addEventListener('submit', event => {
      event.preventDefault();

      const bookings = JSON.parse(localStorage.getItem(storageKey) || '[]');
      bookings.push({
        id: Date.now(),
        tutorId: tutor.id,
        tutorName: tutor.name,
        subject: subjectSelect.value,
        datetime: datetimeInput.value + ':00',
        mode: modeSelect.value,
        status: 'pending'
      });
      localStorage.setItem(storageKey, JSON.stringify(bookings));

      form.reset();
      showMessage('Booking request sent to ' + tutor.name + '. It stays pending until the tutor accepts.', 'Go to My Bookings', 'my-bookings.html');
    });

    form.hidden = false;
  })
  .catch(() => {
    showMessage('Could not load the tutor.');
  });
