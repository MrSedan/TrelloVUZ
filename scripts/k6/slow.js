import http from 'k6/http';
import { check } from 'k6';

const BASE = __ENV.BASE_URL || 'http://nginx';

export const options = {
  vus: Number(__ENV.VUS || 3),
  duration: __ENV.DURATION || '4m',
  summaryTrendStats: ['avg', 'p(50)', 'p(95)', 'p(99)', 'max'],
};

export default function () {
  const res = http.get(`${BASE}/demo/slow.php?seconds=1.5`, { tags: { name: 'slow.php' } });
  check(res, { 'status 200': (r) => r.status === 200 });
}