import type { User } from '../../types'
import { request } from './api'

/**
 * Текущий пользователь из PHP-сессии.
 * Для гостя бэкенд отвечает 401.
 */
export async function getCurrentUser(): Promise<User | null> {
  try {
    const { user } = await request<{ user: User }>('/auth')
    return user
  } catch {
    return null
  }
}

export async function login(email: string, password: string): Promise<User> {
  const { user } = await request<{ user: User }>('/login', {
    method: 'POST',
    body: { email, password },
  })
  return user
}

export async function register(name: string, email: string, password: string): Promise<User> {
  const { user } = await request<{ user: User }>('/register', {
    method: 'POST',
    body: { name, email, password },
  })
  return user
}

export async function logout(): Promise<void> {
  await request('/logout', { method: 'POST' })
}
