import type { ApiError } from './ApiError'

/**
 * Маппинг кодов и сообщений валидации на человекочитаемые сообщения.
 * Коды ошибок берём из бэкенда.
 */
const parameterMessages: Record<string, string> = {
  required: 'Обязательное поле',
  not_nullable: 'Значение не может быть пустым',
  invalid_value: 'Некорректное значение',
  invalid_string: 'Некорректное значение',
  invalid_float: 'Некорректное число',
  invalid_integer: 'Некорректное целое число',
  invalid_boolean: 'Некорректное значение',
  invalid_datetime: 'Некорректная дата и время',
  invalid_datetime_format: 'Некорректный формат даты и времени',
  cannot_be_past: 'Дата не может быть в прошлом',
  invalid_email: 'Некорректный email',
  min_value: 'Значение слишком маленькое',
  max_value: 'Значение слишком большое',
  min_length: 'Слишком короткое значение',
  max_length: 'Слишком длинное значение',
  min_date: 'Дата слишком ранняя',
  max_date: 'Дата слишком поздняя',
  'is required': 'Обязательное поле',
  'must be a valid email': 'Некорректный email',
}

function formatParameterError(message: string): string {
  if (message.startsWith('must be at least ')) {
    message = 'min_length'
  } else if (message.startsWith('must be at most ')) {
    message = 'max_length'
  }

  return parameterMessages[message] ?? 'Проверьте значение'
}

/**
 * { email: ['required', 'invalid_email'] } -> { email: 'Обязательное поле. Некорректный email' }
 */
export function formatParameterErrors(
  parameterErrors: Record<string, string[]>,
): Record<string, string> {
  const formatted: Record<string, string> = {}

  for (const [field, messages] of Object.entries(parameterErrors)) {
    formatted[field] = messages.map(formatParameterError).join('. ')
  }

  return formatted
}

/**
 * Логические ошибки бэкенда -> текст для баннера.
 */
const requestMessages: Record<string, string> = {
  'User already exists': 'Пользователь с таким email уже зарегистрирован',
  'User authorization error': 'Неверный email или пароль',
  'Can`t authorize user': 'Неверный email или пароль',
  'Authorized user': 'Вы уже авторизованы',
  'Can`t authorize new user': 'Не удалось войти под новым пользователем',
  'User not found': 'Пользователь не найден',
  'email already registered': 'Пользователь с таким email уже зарегистрирован',
  'invalid email or password': 'Неверный email или пароль',
}

export function formatRequestError(error: ApiError): string {
  return requestMessages[error.message] ?? 'Не удалось выполнить запрос. Попробуйте ещё раз'
}
