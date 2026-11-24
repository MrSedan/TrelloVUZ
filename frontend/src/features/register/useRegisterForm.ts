import { useState } from 'react'
import type { SubmitEvent } from 'react'
import type { User } from '../../shared/types'

type RegisterFormValues = {
  name: string
  email: string
  password: string
}

const emptyValues: RegisterFormValues = { name: '', email: '', password: '' }

/**
 * Состояние формы регистрации. Вёрстка и все компоненты уже готовы —
 * осталось подключить отправку формы к API.
 *
 * Подсказки:
 *  - useAuth() из shared/lib/auth даёт register(name, email, password);
 *  - shared/lib/api экспортирует ApiError, formatParameterErrors, formatRequestError;
 *  - при успехе вызовите onSuccess(user) — страница сама сделает переход.
 */
export function useRegisterForm(_onSuccess: (user: User) => void) {
  const [values, setValues] = useState<RegisterFormValues>(emptyValues)
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [formError, setFormError] = useState('')
  const [isSubmitting, setIsSubmitting] = useState(false)

  const setValue = (name: string, value: string) => {
    setValues((previous) => ({ ...previous, [name]: value }))
    setErrors((previous) => {
      const next = { ...previous }
      delete next[name]
      return next
    })
  }

  const submit = async (event: SubmitEvent<HTMLFormElement>) => {
    event.preventDefault()
    setFormError('')
    setIsSubmitting(true)

    try {
      // TODO: реализуйте отправку формы.
      // 1. const user = await register(values.name, values.email, values.password)
      //    (register достаётся из useAuth()).
      // 2. При успехе вызовите onSuccess(user).
      // 3. При ошибке разберите ApiError:
      //    setErrors(formatParameterErrors(error.parameterErrors));
      //    setFormError(...) — только если у полей ошибок нет
      //    (formatRequestError(error)),
      //    иначе покажите «Не удалось выполнить запрос. Попробуйте ещё раз».
    } finally {
      setIsSubmitting(false)
    }
  }

  return { values, errors, formError, isSubmitting, setValue, submit }
}
