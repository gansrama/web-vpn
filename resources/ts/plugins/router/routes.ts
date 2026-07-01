export const routes = [
  // Public routes with blank layout
  {
    path: '/',
    component: () => import('@/layouts/blank.vue'),
    children: [
      {
        path: '',
        redirect: '/landing'
      },
      {
        path: 'landing',
        component: () => import('@/pages/landing.vue'),
        meta: { requiresAuth: false }
      },
      {
        path: 'login',
        component: () => import('@/pages/login.vue'),
        meta: { requiresAuth: false }
      },
      {
        path: 'register',
        component: () => import('@/pages/register.vue'),
        meta: { requiresAuth: false }
      },
      {
        path: 'under-development',
        component: () => import('@/pages/under-development.vue'),
        meta: { requiresAuth: false }
      },
      {
        path: '/:pathMatch(.*)*',
        component: () => import('@/pages/[...error].vue'),
      },
    ],
  },
  // Protected routes with default layout
  {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    children: [
      {
        path: 'dashboard',
        component: () => import('@/pages/dashboard.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'data-pegawai',
        component: () => import('@/pages/data-pegawai.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'data-akses-logic',
        component: () => import('@/pages/data-akses-logic.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'data-ip-address',
        component: () => import('@/pages/data-ip-address.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'data-teleworking',
        component: () => import('@/pages/data-teleworking.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'status-form-pegawai',
        component: () => import('@/pages/status-form-pegawai.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'form-akses-logic',
        component: () => import('@/pages/form-akses-logic.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'form-google',
        component: () => import('@/pages/form-google.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'form-teleworking',
        component: () => import('@/pages/form-teleworking.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'account-settings',
        component: () => import('@/pages/account-settings.vue'),
        meta: { requiresAuth: true }
      },
    ],
  },
]
