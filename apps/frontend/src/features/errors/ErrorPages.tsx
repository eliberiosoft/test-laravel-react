import { Link } from 'react-router'
import { LockClosedIcon } from '@heroicons/react/24/outline'
import { EmptyState } from '@/shared/ui/Feedback'
import { buttonClasses } from '@/shared/ui/styles'

export function ForbiddenPage() {
  return (
    <EmptyState
      icon={LockClosedIcon}
      title="No tienes acceso a esta sección"
      description="Tu rol no tiene permiso para ver esta página. Si crees que es un error, contacta a un administrador."
      action={
        <Link to="/welcome" className={buttonClasses()}>
          Ir a la bienvenida
        </Link>
      }
    />
  )
}

export function NotFoundPage() {
  return (
    <div className="grid min-h-full place-items-center px-6 py-24 sm:py-32">
      <div className="text-center">
        <p className="text-base font-semibold text-indigo-600 tabular-nums">404</p>
        <h1 className="mt-4 text-3xl font-semibold tracking-tight text-balance text-gray-900 sm:text-5xl">Página no encontrada</h1>
        <p className="mt-6 text-base text-pretty text-gray-500">La dirección que abriste no existe o fue movida.</p>
        <div className="mt-10">
          <Link to="/welcome" className={buttonClasses()}>
            Volver al inicio
          </Link>
        </div>
      </div>
    </div>
  )
}
