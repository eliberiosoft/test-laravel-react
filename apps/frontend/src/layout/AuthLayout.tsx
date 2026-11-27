import type { ReactNode } from 'react'
import { Outlet } from 'react-router'
import { Brand } from '@/shared/ui/Layout'

export function AuthLayout() {
  return (
    <div className="flex min-h-full flex-col justify-center bg-white px-6 py-12 lg:px-8">
      <div className="sm:mx-auto sm:w-full sm:max-w-sm">
        <Brand centered />
      </div>
      <main className="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <Outlet />
      </main>
    </div>
  )
}

export function AuthHeading({ title, description }: { title: string; description?: ReactNode }) {
  return (
    <div className="mb-8 text-center">
      <h1 className="text-2xl/9 font-bold tracking-tight text-balance text-gray-900">{title}</h1>
      {description && <p className="mt-2 text-sm text-gray-600">{description}</p>}
    </div>
  )
}
