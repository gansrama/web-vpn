export const routes = [
  { path: '/', redirect: '/landing' },
  {
    path: '/',
    component: () => import('@/layouts/blank.vue'),
    children: [
      {
        path: 'landing',
        component: () => import('@/pages/landing.vue'),
      },
      {
        path: 'login',
        component: () => import('@/pages/login.vue'),
      },
      {
        path: 'register',
        component: () => import('@/pages/register.vue'),
      },
      {
        path: 'under-development',
        component: () => import('@/pages/under-development.vue'),
      },
      {
        path: '/:pathMatch(.*)*',
        component: () => import('@/pages/[...error].vue'),
      },
    ],
  },
  {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    children: [
      {
        path: 'dashboard',
        component: () => import('@/pages/dashboard.vue'),
      },
      {
        path: 'data-pegawai',
        component: () => import('@/pages/data-pegawai.vue'),
      },
      {
        path: 'data-akses-logic',
        component: () => import('@/pages/data-akses-logic.vue'),
      },
      {
        path: 'data-ip-address',
        component: () => import('@/pages/data-ip-address.vue'),
      },
      {
        path: 'data-teleworking',
        component: () => import('@/pages/data-teleworking.vue'),
      },
      {
        path: 'status-form-pegawai',
        component: () => import('@/pages/status-form-pegawai.vue'),
      },
      {
        path: 'form-akses-logic',
        component: () => import('@/pages/form-akses-logic.vue'),
      },
      {
        path: 'form-google',
        component: () => import('@/pages/form-google.vue'),
      },
      {
        path: 'form-teleworking',
        component: () => import('@/pages/form-teleworking.vue'),
      },
      {
        path: 'account-settings',
        component: () => import('@/pages/account-settings.vue'),
      },
    ],
  },
]
