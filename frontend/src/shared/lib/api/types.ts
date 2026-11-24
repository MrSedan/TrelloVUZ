/**
 * Общий формат любого ответа от бэкенда
 */
export type ApiEnvelope = {
  status: 'ok' | 'fail'
}

/**
 * Неуспешный ответ: HTTP 4xx/5xx, status = 'fail'.
 */
export type ApiFailure = ApiEnvelope & {
  status: 'fail'
  /** Общий текст ошибки от бэкенда (на английском — переводим в messages.ts). */
  error: string
  /** Ошибки валидации по полям: { email: ['is required'] }. */
  errors?: Record<string, string[]>
}

export type ApiSuccess<T> = ApiEnvelope & { status: 'ok' } & T
