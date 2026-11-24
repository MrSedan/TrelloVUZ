import { ApiError } from './ApiError'
import type { ApiFailure, ApiSuccess } from './types'

const defaultApiUrl = import.meta.env.DEV ? `http://${window.location.hostname}:8080` : ''
const apiUrl: string = import.meta.env.VITE_API_URL ?? defaultApiUrl

type RequestOptions = {
  method?: 'GET' | 'POST'
  body?: unknown
}

/** Отправляет запрос и разбирает JSON-ответ PHP. */
export async function request<T>(
  path: string,
  options: RequestOptions = {},
): Promise<ApiSuccess<T>> {
  const headers = { Accept: 'application/json' }
  const response = await fetch(apiUrl.replace(/\/$/, '') + path, {
    method: options.method ?? 'GET',
    headers:
      options.body === undefined ? headers : { ...headers, 'Content-Type': 'application/json' },
    // Куку сессии браузер отправит сам — JS её не читает
    credentials: 'include',
    body: options.body === undefined ? undefined : JSON.stringify(options.body),
  })

  const data: ApiSuccess<T> | ApiFailure | null = await response.json().catch(() => null)
  if (data?.status === 'fail') {
    throw new ApiError(data)
  }

  if (!response.ok || data?.status !== 'ok') {
    throw new ApiError({ status: 'fail', error: 'Сервер вернул некорректный ответ' })
  }

  return data
}
