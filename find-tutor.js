const tutorsFile = 'mock-tutors.json';

const searchInput = document.getElementById('global-search');
const subjectSelect = document.getElementById('subject-filter');
const sortSelect = document.getElementById('sort-by');
const results = document.getElementById('tutor-results');

let tutors = [];

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

function makeLink(text, href, className) {
  const link = makeElement('a', className, text);
  link.href = href;
  return link;
}

function displayName(fullName) {
  const words = fullName.split(' ');
  const lastName = words.pop();
  return lastName + ', ' + words.join(' ');
}

function initials(fullName) {
  return fullName
    .split(' ')
    .slice(0, 2)
    .map(word => word[0])
    .join('')
    .toUpperCase();
}

const sorters = {
  credibility: (a, b) => b.credibility - a.credibility,
  sessions: (a, b) => b.sessions - a.sessions,
  name: (a, b) => displayName(a.name).localeCompare(displayName(b.name))
};

function fillSubjects() {
  const subjects = [...new Set(tutors.flatMap(tutor => tutor.subjects))].sort();
  subjects.forEach(subject => {
    const option = makeElement('option', '', subject);
    option.value = subject;
    subjectSelect.appendChild(option);
  });
}

function matches(tutor) {
  const search = searchInput.value.trim().toLowerCase();
  if (subjectSelect.value && !tutor.subjects.includes(subjectSelect.value)) {
    return false;
  }
  if (search) {
    const text = (tutor.name + ' ' + tutor.subjects.join(' ')).toLowerCase();
    return text.includes(search);
  }
  return true;
}

function makeCard(tutor) {
  const card = makeElement('article', 'tutor-card');

  const top = makeElement('div', 'tutor-top');
  top.appendChild(makeElement('div', 'tutor-avatar', initials(tutor.name)));

  const info = makeElement('div', 'tutor-info');
  info.appendChild(makeLink(displayName(tutor.name), 'tutor-profile.html?id=' + tutor.id, 'tutor-name'));

  const meta = makeElement('div', 'tutor-meta');
  meta.appendChild(makeElement('span', 'tutor-cred', '\u2605 ' + tutor.credibility.toFixed(1) + ' \u00B7 ' + tutor.sessions + ' sessions'));
  meta.appendChild(makeElement(
    'span',
    tutor.verified ? 'tutor-badge verified' : 'tutor-badge pending',
    tutor.verified ? 'Verified \u2713' : 'Pending'
  ));
  info.appendChild(meta);
  top.appendChild(info);
  card.appendChild(top);

  const tags = makeElement('div', 'tutor-tags');
  tutor.subjects.forEach(subject => {
    tags.appendChild(makeElement('span', 'subject-pill', subject));
  });
  card.appendChild(tags);

  const actions = makeElement('div', 'card-actions');
  const mail = makeLink('\u2709\uFE0E', 'messages.html', 'mail-btn');
  mail.title = 'Message tutor';
  actions.appendChild(mail);

  if (tutor.verified) {
    actions.appendChild(makeLink('Book session', 'book-consultation.html?id=' + tutor.id, 'btn-book'));
  } else {
    const disabledButton = makeElement('button', 'btn-book', 'Not yet bookable');
    disabledButton.disabled = true;
    actions.appendChild(disabledButton);
  }
  card.appendChild(actions);

  return card;
}

function render() {
  const shown = tutors.filter(matches).sort(sorters[sortSelect.value]);
  if (shown.length === 0) {
    results.replaceChildren(makeElement('p', 'empty-message', 'No tutors match your search.'));
    return;
  }
  results.replaceChildren(...shown.map(makeCard));
}

searchInput.addEventListener('input', render);
subjectSelect.addEventListener('change', render);
sortSelect.addEventListener('change', render);

searchInput.value = new URLSearchParams(window.location.search).get('q') || '';

fetch(tutorsFile)
  .then(response => response.json())
  .then(data => {
    tutors = data;
    fillSubjects();
    render();
  })
  .catch(() => {
    results.replaceChildren(makeElement('p', 'empty-message', 'Could not load the tutors.'));
  });
