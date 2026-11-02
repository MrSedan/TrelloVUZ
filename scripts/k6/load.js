import http from 'k6/http';
import { check } from 'k6';

const BASE = __ENV.BASE_URL || 'http://nginx';

export const options = {
  scenarios: {
    load: {
      executor: 'ramping-vus',
      stages: [
        { duration: '30s', target: Number(__ENV.VUS || 20) }, // плавный разгон
        { duration: __ENV.DURATION || '4m', target: Number(__ENV.VUS || 20) },
        { duration: '10s', target: 0 },
      ],
    },
  },
  // В итоговой таблице — перцентили, которые обсуждаем на лекции
  summaryTrendStats: ['avg', 'p(50)', 'p(95)', 'p(99)', 'max'],
};

export default function () {
  const res = http.get(`${BASE}/demo/cpu.php?ms=300`, { tags: { name: 'cpu.php' } });
  check(res, { 'status 200': (r) => r.status === 200 });
}