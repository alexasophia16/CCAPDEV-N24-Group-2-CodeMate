const bookingsFile = 'mock-bookings.json';
const storageKey = 'codemate_new_bookings';

const tableWrap = document.getElementById('bookings-table-wrap');
const tableBody = document.getElementById('bookings-body');
const message = document.getElementById('bookings-message');

function makeElement(tag, className, text) {
  const element = document.createElement(tag);
  if (className) {
    element.className = className;
  }
  if (text) {
    element.textContent = text;
  }
  return element;
}

function makeCell(label) {
  const cell = document.createElement('td');
  cell.dataset.label = label;
  return cell;
}

function formatSchedule(datetime) {
  const date = new Date(datetime);
  const day = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const time = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
  return day + ' \u00B7 ' + time;
}

function readSavedBookings() {
  try {
    return JSON.parse(localStorage.getItem(storageKey) || '[]');
  } catch (error) {
    return [];
  }
}

function makeRow(booking) {
  const row = document.createElement('tr');

  const tutorCell = makeCell('Tutor');
  const tutorLink = makeElement('a', 'tutor-link', booking.tutorName);
  tutorLink.href = 'tutor-profile.html?id=' + booking.tutorId;
  tutorCell.appendChild(tutorLink);
  row.appendChild(tutorCell);

  const subjectCell = makeCell('Subject');
  subjectCell.textContent = booking.subject;
  row.appendChild(subjectCell);

  const scheduleCell = makeCell('Schedule');
  scheduleCell.textContent = formatSchedule(booking.datetime);
  row.appendChild(scheduleCell);

  const modeCell = makeCell('Mode');
  modeCell.textContent = booking.mode;
  row.appendChild(modeCell);

  const statusCell = makeCell('Status');
  statusCell.appendChild(makeElement('span', 'status-pill status-' + booking.status, booking.status));
  row.appendChild(statusCell);

  return row;
}

function showEmptyMessage() {
  message.textContent = 'You have no bookings yet. ';
  const link = makeElement('a', '', 'Find a tutor');
  link.href = 'find-tutor.html';
  message.appendChild(link);
}

fetch(bookingsFile)
  .then(response => response.json())
  .then(data => {
    const bookings = data
      .concat(readSavedBookings())
      .sort((a, b) => b.datetime.localeCompare(a.datetime));

    if (bookings.length === 0) {
      showEmptyMessage();
      return;
    }

    tableBody.replaceChildren(...bookings.map(makeRow));
    tableWrap.hidden = false;
  })
  .catch(() => {
    message.textContent = 'Could not load your bookings.';
  });
