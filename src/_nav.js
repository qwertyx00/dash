export default [
  {
    component: 'CNavItem',
    name: 'Dashboard',
    to: '/dashboard',
    icon: 'cil-speedometer',
  },
    {
    component: 'CNavItem',
    name: 'Website',
    href: 'https://knaus-filtertechnik.com',
    external: true,
    icon: 'cil-speedometer',
  },
  {
    component: 'CNavTitle',
    name: 'Menü',
  },
  {
    component: 'CNavGroup',
    name: 'Produkte',
    to: '/products',
    icon: 'cil-puzzle',
    items: [
      {
        component: 'CNavItem',
        name: 'Alle Produkte anzeigen',
        to: '/base/accordion',
      },
      {
        component: 'CNavItem',
        name: 'Datenbank',
        href: 'https://coreui.io/vue/docs/components/calendar.html',
        external: true,
        badge: {
          color: 'danger',
          text: 'DB',
        },
      },
    ],
  },
  {
    component: 'CNavGroup',
    name: 'Aktuelles',
    to: '/news',
    icon: 'cil-cursor',
    items: [
      {
        component: 'CNavItem',
        name: 'News bearbeiten',
        to: '/buttons/standard-buttons',
      },
    ],
  },
  {
    component: 'CNavGroup',
    name: 'Nachrichten',
    to: '/notifications',
    icon: 'cil-bell',
    items: [
    ],
  },
  {
    component: 'CNavItem',
    name: 'Widgets',
    to: '/widgets',
    icon: 'cil-calculator',
    badge: {
      color: 'primary',
      text: 'NEW',
      shape: 'pill',
    },
  },
  {
    component: 'CNavTitle',
    name: 'Extras',
  },
  {
    component: 'CNavGroup',
    name: 'Dokumentation',
    to: '/pages',
    icon: 'cil-star',
    items: [
      {
        component: 'CNavItem',
        name: 'Login',
        to: '/pages/login',
      },
      {
        component: 'CNavItem',
        name: 'Register',
        to: '/pages/register',
      },
      {
        component: 'CNavItem',
        name: 'Error 404',
        to: '/pages/404',
      },
      {
        component: 'CNavItem',
        name: 'Error 500',
        to: '/pages/500',
      },
    ],
  },
]
