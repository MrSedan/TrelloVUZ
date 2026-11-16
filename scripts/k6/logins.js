import http from 'k6/http';
import { check } from 'k6';

const BASE = __ENV.BASE_URL || 'http://nginx';
const PASSWORD = 'password1';

export const options = {
  scenarios: {
    // Нормальные успешные входы — на их фоне видно, что растут именно неудачные
    background: {
      executor: 'constant-arrival-rate',
      exec: 'background',
      rate: 1, timeUnit: '1s', duration: '4m',
      preAllocatedVUs: 5,
    },
    // Подбор пароля: 3 неудачные попытки в секунду при пороге алерта 0.2/с
    bruteforce: {
      executor: 'constant-arrival-rate',
      exec: 'bruteforce',
      rate: 3, timeUnit: '1s', duration: '4m',
      preAllocatedVUs: 5,
    },
  },
};

export function setup() {
  // Уникальный email на запуск, чтобы сценарий можно было повторять
  const email = `k6-${Date.now()}@example.com`;
  const res = http.post(`${BASE}/register`, { email, password: PASSWORD, name: 'k6' });
  check(res, { 'user registered': (r) => r.status === 201 });

  return { email };
}

export function background(data) {
  http.post(`${BASE}/login`, { email: data.email, password: PASSWORD }, { tags: { name: 'login ok' } });
}

export function bruteforce(data) {
  http.post(`${BASE}/login`, { email: data.email, password: 'wrong-password' }, { tags: { name: 'login failed' } });
}
