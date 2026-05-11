const API_BASE = '../backend/api';
let currentUser = JSON.parse(localStorage.getItem('clinic_user') || 'null');

const statusText = {
  pending: 'بانتظار التأكيد',
  confirmed: 'مؤكد',
  completed: 'مكتمل',
  cancelled: 'ملغي',
  no_show: 'لم يحضر'
};

async function api(path, options = {}) {
  const res = await fetch(`${API_BASE}/${path}`, {
    headers: { 'Content-Type': 'application/json' },
    ...options
  });
  return res.json();
}

function showMessage(message, ok = true) {
  alert((ok ? '✅ ' : '⚠️ ') + message);
}

async function loadDoctors() {
  const result = await api('doctors.php');
  const select = document.getElementById('doctorSelect');
  select.innerHTML = '';
  if (result.success) {
    result.data.forEach(d => {
      const option = document.createElement('option');
      option.value = d.id;
      option.textContent = d.name;
      select.appendChild(option);
    });
  }
}

async function login() {
  const email = document.getElementById('loginEmail').value;
  const password = document.getElementById('loginPassword').value;
  const role = document.getElementById('loginRole').value;

  const result = await api('login.php', {
    method: 'POST',
    body: JSON.stringify({ email, password, role })
  });

  if (!result.success) return showMessage(result.message, false);
  currentUser = result.data;
  localStorage.setItem('clinic_user', JSON.stringify(currentUser));
  showMessage(result.message);
  renderDashboard();
}

async function registerPatient() {
  const name = document.getElementById('regName').value;
  const email = document.getElementById('regEmail').value;
  const phone = document.getElementById('regPhone').value;
  const password = document.getElementById('regPassword').value;

  const result = await api('register.php', {
    method: 'POST',
    body: JSON.stringify({ name, email, phone, password })
  });

  showMessage(result.message, result.success);
}

async function createAppointment() {
  if (!currentUser || currentUser.role !== 'patient') {
    return showMessage('يجب تسجيل الدخول كمريض قبل الحجز', false);
  }

  const doctor_id = document.getElementById('doctorSelect').value;
  const appointment_date = document.getElementById('appointmentDate').value;
  const appointment_time = document.getElementById('appointmentTime').value;
  const reason = document.getElementById('reason').value;

  const result = await api('appointments.php', {
    method: 'POST',
    body: JSON.stringify({
      doctor_id,
      patient_id: currentUser.id,
      appointment_date,
      appointment_time,
      reason
    })
  });

  showMessage(result.message, result.success);
  if (result.success) renderDashboard();
}

function logout() {
  localStorage.removeItem('clinic_user');
  currentUser = null;
  document.getElementById('dashboard').classList.add('hidden');
  showMessage('تم تسجيل الخروج');
}

async function renderDashboard() {
  if (!currentUser) return;
  document.getElementById('dashboard').classList.remove('hidden');
  document.getElementById('dashboardTitle').textContent = currentUser.role === 'doctor' ? 'لوحة الدكتور' : 'لوحة المريض';
  document.getElementById('welcomeText').textContent = `مرحبًا، ${currentUser.name}`;
  document.getElementById('doctorTools').classList.toggle('hidden', currentUser.role !== 'doctor');
  document.getElementById('actionHeader').style.display = currentUser.role === 'doctor' ? '' : 'none';
  await loadAppointments();
  if (currentUser.role === 'doctor') await loadAvailability();
}

async function loadAppointments() {
  const result = await api(`appointments.php?role=${currentUser.role}&user_id=${currentUser.id}`);
  const tbody = document.getElementById('appointmentsTable');
  tbody.innerHTML = '';

  if (!result.success) return;
  const list = result.data;
  document.getElementById('totalAppointments').textContent = list.length;
  document.getElementById('confirmedAppointments').textContent = list.filter(a => a.status === 'confirmed').length;
  document.getElementById('pendingAppointments').textContent = list.filter(a => a.status === 'pending').length;

  list.forEach(a => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${a.appointment_date}</td>
      <td>${a.appointment_time.slice(0,5)}</td>
      <td>${a.doctor_name}</td>
      <td>${a.patient_name}</td>
      <td>${a.reason || '-'}</td>
      <td><span class="status ${a.status}">${statusText[a.status]}</span></td>
      <td class="doctor-action">${currentUser.role === 'doctor' ? statusSelect(a) : ''}</td>
    `;
    tbody.appendChild(tr);
  });

  document.querySelectorAll('.doctor-action').forEach(td => {
    td.style.display = currentUser.role === 'doctor' ? '' : 'none';
  });
}

function statusSelect(a) {
  return `
    <select class="small-select" onchange="updateStatus(${a.id}, this.value)">
      ${Object.keys(statusText).map(s => `<option value="${s}" ${a.status === s ? 'selected' : ''}>${statusText[s]}</option>`).join('')}
    </select>
  `;
}

async function updateStatus(id, status) {
  const result = await api('appointments.php', {
    method: 'PUT',
    body: JSON.stringify({ id, status })
  });
  showMessage(result.message, result.success);
  if (result.success) loadAppointments();
}

async function addAvailability() {
  const day_name = document.getElementById('availableDay').value;
  const start_time = document.getElementById('startTime').value;
  const end_time = document.getElementById('endTime').value;
  const slot_duration = document.getElementById('slotDuration').value;

  const result = await api('availability.php', {
    method: 'POST',
    body: JSON.stringify({
      doctor_id: currentUser.id,
      day_name,
      start_time,
      end_time,
      slot_duration
    })
  });

  showMessage(result.message, result.success);
  if (result.success) loadAvailability();
}

async function loadAvailability() {
  const result = await api(`availability.php?doctor_id=${currentUser.id}`);
  const box = document.getElementById('availabilityList');
  box.innerHTML = '';
  if (!result.success) return;

  result.data.forEach(item => {
    const div = document.createElement('div');
    div.className = 'availability-item';
    div.innerHTML = `
      <span>${item.day_name} من ${item.start_time.slice(0,5)} إلى ${item.end_time.slice(0,5)} - ${item.slot_duration} دقيقة</span>
      <button class="btn danger" onclick="deleteAvailability(${item.id})">حذف</button>
    `;
    box.appendChild(div);
  });
}

async function deleteAvailability(id) {
  const result = await api(`availability.php?id=${id}`, { method: 'DELETE' });
  showMessage(result.message, result.success);
  if (result.success) loadAvailability();
}

loadDoctors();
renderDashboard();
