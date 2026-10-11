const tutorsFile = 'mock-tutors.json';
const reviewsFile = 'mock-reviews.json';
const tutorId = Number(new URLSearchParams(window.location.search).get('id'));

const profile = document.getElementById('profile');
const message = document.getElementById('profile-message');

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

function initials(fullName) {
  return fullName
    .split(' ')
    .slice(0, 2)
    .map(word => word[0])
    .join('')
    .toUpperCase();
}

function formatTime(time) {
  const parts = time.split(':').map(Number);
  const suffix = parts[0] >= 12 ? 'PM' : 'AM';
  const hour = parts[0] % 12 || 12;
  return hour + ':' + String(parts[1]).padStart(2, '0') + ' ' + suffix;
}

function formatDate(dateText) {
  return new Date(dateText + 'T00:00:00').toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
}

function stars(rating) {
  return '\u2605'.repeat(rating) + '\u2606'.repeat(5 - rating);
}

function fillSubjects(tutor) {
  const container = document.getElementById('tutor-subjects');
  tutor.subjects.forEach(subject => {
    container.appendChild(makeElement('span', 'subject-pill', subject));
  });
}

function fillSlots(tutor) {
  const list = document.getElementById('tutor-slots');
  if (tutor.availability.length === 0) {
    list.appendChild(makeElement('li', '', 'No open slots right now.'));
    return;
  }
  tutor.availability.forEach(slot => {
    const item = makeElement('li');
    item.appendChild(makeElement('span', 'slot-day', slot.day));
    item.appendChild(makeElement('span', 'slot-time', formatTime(slot.start) + ' - ' + formatTime(slot.end)));
    list.appendChild(item);
  });
}

function fillReviews(tutorReviews) {
  const summary = document.getElementById('review-summary');
  const container = document.getElementById('tutor-reviews');

  if (tutorReviews.length === 0) {
    summary.textContent = 'No reviews yet.';
    return;
  }

  const total = tutorReviews.reduce((sum, review) => sum + review.rating, 0);
  const average = (total / tutorReviews.length).toFixed(1);
  summary.textContent = tutorReviews.length === 1
    ? '1 review \u00B7 average ' + average + ' / 5'
    : tutorReviews.length + ' reviews \u00B7 average ' + average + ' / 5';

  tutorReviews
    .sort((a, b) => b.date.localeCompare(a.date))
    .forEach(review => {
      const item = makeElement('article', 'review');
      item.appendChild(makeElement('div', 'review-stars', stars(review.rating)));
      item.appendChild(makeElement('p', 'review-comment', review.comment));
      item.appendChild(makeElement('div', 'review-meta', review.reviewer + ' \u00B7 ' + review.subject + ' \u00B7 ' + formatDate(review.date)));
      container.appendChild(item);
    });
}

Promise.all([
  fetch(tutorsFile).then(response => response.json()),
  fetch(reviewsFile).then(response => response.json())
])
  .then(([tutors, reviews]) => {
    const tutor = tutors.find(item => item.id === tutorId);
    if (!tutor) {
      message.textContent = 'Tutor not found.';
      return;
    }

    document.title = 'CodeMate - ' + tutor.name;
    document.getElementById('tutor-avatar').textContent = initials(tutor.name);
    document.getElementById('tutor-name').textContent = tutor.name;
    document.getElementById('tutor-rating').textContent = '\u2605 ' + tutor.credibility.toFixed(1) + ' \u00B7 ' + tutor.sessions + ' sessions';

    const status = document.getElementById('tutor-status');
    status.textContent = tutor.verified ? 'Verified \u2713' : 'Pending';
    status.className = 'tutor-badge ' + (tutor.verified ? 'verified' : 'pending');

    document.getElementById('tutor-bio').textContent = tutor.bio;
    document.getElementById('tutor-experience').textContent = tutor.experience;

    const bookLink = document.getElementById('book-link');
    if (tutor.verified) {
      bookLink.href = 'book-consultation.html?id=' + tutor.id;
    } else {
      bookLink.hidden = true;
      document.getElementById('unverified-note').hidden = false;
    }

    fillSubjects(tutor);
    fillSlots(tutor);
    fillReviews(reviews.filter(review => review.tutorId === tutor.id));

    profile.hidden = false;
  })
  .catch(() => {
    message.textContent = 'Could not load the tutor.';
  });
