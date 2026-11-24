import type { ApiFailure } from './types'

/** Ошибка API из JSON-ответа PHP. */
export class ApiError extends Error {
  /** Ошибки валидации по полям — их показываем прямо под input'ами. */
  readonly parameterErrors: Record<string, string[]>

  constructor(failure: ApiFailure) {
    super(failure.error)
    this.name = 'ApiError'
    this.parameterErrors = failure.errors ?? {}
  }
}
