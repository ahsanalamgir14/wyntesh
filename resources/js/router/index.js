import Vue from 'vue';
import Router from 'vue-router';

/**
 * Layzloading will create many files and slow on compiling, so best not to use lazyloading on devlopment.
 * The syntax is lazyloading, but we convert to proper require() with babel-plugin-syntax-dynamic-import
 * @see https://doc.laravue.dev/guide/advanced/lazy-loading.html
 */

Vue.use(Router);

/* Layout */
import Layout from '@/layout';


/**
 * Sub-menu only appear when children.length>=1
 * @see https://doc.laravue.dev/guide/essentials/router-and-nav.html
 **/

/**
* hidden: true                   if `hidden:true` will not show in the sidebar(default is false)
* alwaysShow: true               if set true, will always show the root menu, whatever its child routes length
*                                if not set alwaysShow, only more than one route under the children
*                                it will becomes nested mode, otherwise not show the root menu
* redirect: noredirect           if `redirect:noredirect` will no redirect in the breadcrumb
* name:'router-name'             the name is used by <keep-alive> (must set!!!)
* meta : {
    roles: ['admin', 'editor']   Visible for these roles only
    permissions: ['view menu zip', 'manage user'] Visible for these permissions only
    title: 'title'               the name show in sub-menu and breadcrumb (recommend set)
    icon: 'svg-name'             the icon show in the sidebar
    noCache: true                if true, the page will no be cached(default is false)
    breadcrumb: false            if false, the item will hidden in breadcrumb (default is true)
    affix: true                  if true, the tag will affix in the tags-view
  }
**/

export const constantRoutes = [
  {
    path: '/redirect',
    component: Layout,
    hidden: true,
    children: [
      {
        path: '/redirect/:path*',
        component: () => import('@/views/redirect/index')
      }
    ]
  },
  {
    path: '/login',
    component: () => import('@/views/auth/login/index'),
    hidden: true
  },
  {
    path: '/register',
    component: () => import('@/views/auth/register/index'),
    hidden: true
  },
  {
    path: '/forgot-password',
    component: () => import('@/views/auth/forgot-pass/index'),
    hidden: true
  },
  {
    path: '/auth-redirect',
    component: () => import('@/views/auth/login/AuthRedirect'),
    hidden: true
  },
  {
    path: '/404',
    component: () => import('@/views/error-page/404'),
    hidden: true
  },
  {
    path: '/401',
    component: () => import('@/views/error-page/401'),
    hidden: true
  },
  {
    path: '/',
    component: Layout,
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        component: () => import('@/views/dashboard/index'),
        name: 'Dashboard',
        meta: { title: 'Dashboard', icon: 'fas fa-tachometer-alt', color:'color:#39A8FA', affix: true }
      }
    ]
  }
]

/**
 * asyncRoutes
 * the routes that need to be dynamically loaded based on user roles
 */
export const asyncRoutes = [
    
  {
    path: '/users-and-roles',
    component: Layout,
    meta: {
      roles: ['superadmin']
    },
    children: [
      {
        path: 'manage',
        component: () => import('@/views/superadmin/admins-and-roles/index'),
        name: 'Admin and Roles',
        meta: { title: 'Admin and Roles', color:'color:#EE7642', icon: 'fas fa-users', affix: true, roles: ['superadmin'] }
      }
    ]
  },
  {
    path: '/configs',
    component: Layout,
    meta: {
      title: 'Configrations',
      icon: 'fas fa-cogs',
      roles: ['superadmin'],
      color:'color:#854CE2'
    },
    children: [
      {
        path: 'transaction-types',
        component: () => import('@/views/superadmin/configs/transaction-types'),
        name: 'Transaction Types',
        meta: { title: 'Transaction Types', color:'color:#34A540', icon: 'fas fa-list-alt', affix: true, roles: ['superadmin'] }
      },
      {
        path: 'payment-modes',
        component: () => import('@/views/superadmin/configs/payment-modes'),
        name: 'Payment Modes',
        meta: { title: 'Payment Modes', color:'color:#EE7642', icon: 'fas fa-money-bill-wave', affix: true, roles: ['superadmin'] }
      },
      {
        path: 'packages',
        component: () => import('@/views/admin/packages/index'),
        name: 'Packages',
        meta: { title: 'Packages', icon: 'fas fa-box', color:'color:#854CE2', affix: true, roles: ['superadmin'] }
      }
    ]
  },
  {
    path: '/members',
    component: Layout,
    name: 'Members',
    redirect: '/members/manage',
    meta: {
      title: 'Members',
      icon: 'fas fa-users',
      roles: ['admin'],
      color:'color:#854CE2'
    },
    children: [
      {
        path: 'manage',
        component: () => import('@/views/admin/users/index'),
        name: 'Members',
        meta: { title: 'Members', icon: 'fas fa-user', color:'color:#EE7642', affix: true, roles: ['admin'] }
      },
      {
        path: 'add',
        component: () => import('@/views/admin/members/add'),
        name: 'Add Members',
        meta: { title: 'Add Members', icon: 'fas fa-plus', color:'color:#EE7642', affix: true, roles: ['admin'] }
      }
    ]
  },
  {
    path: '/kyc',
    component: Layout,
    name: 'KYCs',
    redirect: '/kyc/pending',
    meta: {
      title: 'KYCs',
      icon: 'fas fa-images',
      roles: ['admin'],
      color:'color:#34A540'
    },
    hidden: false,
    children: [
      {
        path: 'pending',
        component: () => import('@/views/admin/kycs/pending'),
        name: 'Pending',
        meta: { title: 'Pending', icon: 'fas fa-thumbtack', color:'color:#1A9672', affix: true, roles: ['admin'] }
      },
      {
        path: 'approved',
        component: () => import('@/views/admin/kycs/approved'),
        name: 'Approved',
        meta: { title: 'Approved', icon: 'far fa-thumbs-up', color:'color:#1CA8B6', affix: true, roles: ['admin'] }
      },
      {
        path: 'rejected',
        component: () => import('@/views/admin/kycs/rejected'),
        name: 'Rejected',
        meta: { title: 'Rejected', icon: 'far fa-thumbs-down', color:'color:#1CA8B6', affix: true, roles: ['admin'] }
      }
    ]
  },
  {
    path: '/packages',
    component: Layout,
    meta: {
      roles: ['admin']
    },
    children: [
      {
        path: 'manage',
        component: () => import('@/views/admin/packages/index'),
        name: 'Packages',
        meta: { title: 'Packages', icon: 'fas fa-box', color:'color:#854CE2', affix: true, roles: ['admin'] }
      }
    ]
  },
  {
    path: '/geneology',
    component: Layout,
    meta: {
      roles: ['admin']
    },
    children: [
      {
        path: 'view',
        component: () => import('@/views/admin/geneology/index'),
        name: 'Geneology',
        meta: { title: 'Geneology', icon: 'fas fa-sitemap', color:'color:#854CE2', affix: true, roles: ['admin','superadmin'] }
      },
      {
        path: 'member/:id',
        component: () => import('@/views/admin/geneology/member'),
        name: 'Member Geneology',
        hidden:true,
        meta: { title: 'Member Geneology', icon: 'fas fa-sitemap', color:'color:#854CE2', affix: true, roles: ['admin','superadmin'] }
      }
    ]
  },

  {
    path: '/pins',
    component: Layout,
    name: 'PINs',
    meta: {
      title: 'PINs',
      icon: 'fas fa-tags',
      roles: ['admin'],
      color:'color:#CF1F5C'
    },
    hidden: false,
    children: [
      {
        path: 'all',
        component: () => import('@/views/admin/pins/all'),
        name: 'My Payout',
        meta: { title: 'Generate PINs', icon: 'fas fa-tag', color:'color:#35BED1', affix: true, roles: ['admin'] }
      },
      {
        path: 'pending-pin-requests',
        component: () => import('@/views/admin/pins/pending-pin-requests'),
        name: 'PIN Requests',
        meta: { title: 'Pending Requests', icon: 'fas fa-tag', color:'color:#DC7633', affix: true, roles: ['admin'] }
      },
      {
        path: 'approved-pin-requests',
        component: () => import('@/views/admin/pins/approved-pin-requests'),
        name: 'Approved Requests',
        meta: { title: 'Approved Requests', icon: 'fas fa-tag', color:'color:#40BF27', affix: true, roles: ['admin'] }
      },
      {
        path: 'rejected-pin-requests',
        component: () => import('@/views/admin/pins/rejected-pin-requests'),
        name: 'Rejected Requests',
        meta: { title: 'Rejected Requests', icon: 'fas fa-tag', color:'color:#CF1F5C', affix: true, roles: ['admin'] }
      }
    ]
  },
  {
    path: '/payouts',
    component: Layout,
    name: 'Payouts',
    meta: {
      title: 'Payouts',
      icon: 'fas fa-rupee-sign',
      roles: ['admin'],
      color:'color:#078F6A'
    },
    hidden: false,
    children: [
      {
        path: 'generate',
        component: () => import('@/views/admin/payouts/generate'),
        name: 'Generate PINs',
        meta: { title: 'Generate Payout', icon: 'far fa-check-circle', color:'color:#DCB527', affix: true, roles: ['admin'] }
      },
      // {
      //   path: 'all',
      //   component: () => import('@/views/admin/users/index'),
      //   name: 'PIN Requests',
      //   meta: { title: 'All Payouts', icon: 'fas fa-list', color:'color:#226CBF', affix: true, roles: ['admin'] }
      // }
    ]
  },
  {
    path: '/wallet',
    component: Layout,
    name: 'Wallet',
    meta: {
      title: 'Wallet',
      icon: 'fas fa-wallet',
      roles: ['admin'],
      color:'color:#DC7633'
    },
    hidden: false,
    children: [
      {
        path: 'transactions',
        component: () => import('@/views/admin/wallet/transactions'),
        name: 'Transactions',
        meta: { title: 'Transactions', icon: 'far fa-list-alt', color:'color:#FF5733', affix: true, roles: ['admin'] }
      },
      {
        path: 'all',
        component: () => import('@/views/admin/wallet/all-transactions'),
        name: 'PIN Requests',
        meta: { title: 'All Payouts', icon: 'fas fa-hand-holding-usd', color:'color:#C39BD3', affix: true, roles: ['admin'] }
      }
    ]
  },
  {
    path: '/site',
    component: Layout,
    name: 'Manage Site',
    meta: {
      title: 'Manage',
      icon: 'fas fa-globe',
      roles: ['admin'],
      color:'color:#dd6161'
    },
    hidden: false,
    children: [
      {
        path: 'news-and-updates',
        component: () => import('@/views/admin/newses/index'),
        name: 'News & Updates',
        meta: { title: 'News', icon: 'fas fa-newspaper', color:'color:#48C9B0', affix: true, roles: ['admin'] }
      },
      {
        path: 'achievers',
        component: () => import('@/views/admin/achievers/index'),
        name: 'Achievers',
        meta: { title: 'Achievers', icon: 'fas fa-award', color:'color:#48C9B0', affix: true, roles: ['admin'] }
      },
      {
        path: 'downloads',
        component: () => import('@/views/admin/downloads/index'),
        name: 'Downloads',
        meta: { title: 'Downloads', icon: 'fas fa-download', color:'color:#EC7063', affix: true, roles: ['admin'] }
      },
      {
        path: 'gallery',
        component: () => import('@/views/admin/gallery/index'),
        name: 'Gallery',
        meta: { title: 'Gallery', icon: 'fas fa-photo-video', color:'color:#A569BD', affix: true, roles: ['admin'] }
      },
      {
        path: 'popups',
        component: () => import('@/views/admin/popups/index'),
        name: 'Popups',
        meta: { title: 'Popups', icon: 'fas fa-image', color:'color:#3498DB', affix: true, roles: ['admin'] }
      },
      {
        path: 'sliders',
        component: () => import('@/views/admin/sliders/index'),
        name: 'Sliders',
        meta: { title: 'Sliders', icon: 'fas fa-images', color:'color:#16A085', affix: true, roles: ['admin'] }
      },
      {
        path: 'testimonials',
        component: () => import('@/views/admin/testimonials/index'),
        name: 'Testimonials',
        meta: { title: 'Testimonials', icon: 'fas fa-user-friends', color:'color:#27AE60', affix: true, roles: ['admin'] }
      },
      {
        path: 'bank-partners',
        component: () => import('@/views/admin/bank-partners/index'),
        name: 'Bank Partners',
        meta: { title: 'Bank Partners', icon: 'fas fa-university', color:'color:#196F3D', affix: true, roles: ['admin'] }
      },
      {
        path: 'company-details',
        component: () => import('@/views/admin/settings/company-details'),
        name: 'Company Details',
        meta: { title: 'Company Details', icon: 'fas fa-building', color:'color:#F1C40F', affix: true, roles: ['admin'] }
      },
      {
        path: 'settings',
        component: () => import('@/views/admin/users/index'),
        name: 'Settings',
        hidden:true,
        meta: { title: 'Settings', icon: 'fas fa-user-cog', color:'color:#ECF0F1', affix: true, roles: ['admin'] }
      }
    ]
  },
  {
    path: '/support',
    component: Layout,
    name: 'Support',
    meta: {
      title: 'Support',
      icon: 'fas fa-headset',
      roles: ['admin'],
      color:'color:#DC7633'
    },
    hidden: false,
    children: [
      {
        path: 'inquiries',
        component: () => import('@/views/admin/inquiries/index'),
        name: 'Inquiries',
        meta: { title: 'Inquiries', icon: 'fas fa-phone-square-alt', color:'color:#FF5733', affix: true, roles: ['admin'] }
      },
      {
        path: 'tickets',
        component: () => import('@/views/admin/support/index'),
        name: 'Tickets',
        meta: { title: 'Tickets', icon: 'fas fa-envelope-open-text', color:'color:#C39BD3', affix: true, roles: ['admin'] }
      }
    ]
  },

  //User Routes
  {
    path: '/my',
    component: Layout,
    name: 'Manage',
    meta: {
      title: 'Manage',
      icon: 'fas fa-user',
      roles: ['user'],
      color:'color:#DC7633'
    },
    hidden: false,
    children: [
      {
        path: 'profile',
        component: () => import('@/views/user/profile/Profile'),
        name: 'Profile & KYC',
        meta: { title: 'Profile & KYC', icon: 'fas fa-user', color:'color:#FF5733', affix: true, roles: ['user'] }
      },
    ]
  },
  {
    path: '/members',
    component: Layout,
    name: 'Members',
    redirect: '/members/add',
    meta: {
      title: 'Members',
      icon: 'fas fa-users',
      roles: ['user'],
      color:'color:#854CE2'
    },
    children: [     
      {
        path: 'add',
        component: () => import('@/views/admin/members/add'),
        name: 'Add Member',
        meta: { title: 'Add Member', icon: 'fas fa-plus', color:'color:#EE7642', affix: true, roles: ['user'] }
      }
    ]
  },
  {
    path: '/my/geneology',
    component: Layout,
    meta: {
      roles: ['user']
    },
    children: [
      {
        path: 'view',
        component: () => import('@/views/user/geneology/index'),
        name: 'Geneology',
        meta: { title: 'Geneology', icon: 'fas fa-sitemap', color:'color:#854CE2', affix: true, roles: ['user'] }
      },
      {
        path: 'member/:id',
        component: () => import('@/views/user/geneology/member'),
        name: 'Member Geneology',
        hidden:true,
        meta: { title: 'Member Geneology', icon: 'fas fa-sitemap', color:'color:#854CE2', affix: true, roles: ['user'] }
      }
    ]
  },
  {
    path: '/member/pins',
    component: Layout,
    name: 'PINs',
    meta: {
      title: 'PINs',
      icon: 'fas fa-tags',
      roles: ['user'],
      color:'color:#CF1F5C'
    },
    hidden: false,
    children: [
      {
        path: 'all',
        component: () => import('@/views/user/pins/all'),
        name: 'My PINs',
        meta: { title: 'My PINs', icon: 'fas fa-tag', color:'color:#35BED1', affix: true, roles: ['user'] }
      },
      {
        path: 'pending-pin-requests',
        component: () => import('@/views/user/pins/pending-pin-requests'),
        name: 'PIN Requests',
        meta: { title: 'Create Request', icon: 'fas fa-tag', color:'color:#DC7633', affix: true, roles: ['user'] }
      },
      {
        path: 'approved-pin-requests',
        component: () => import('@/views/user/pins/approved-pin-requests'),
        name: 'Approved Requests',
        meta: { title: 'Approved Requests', icon: 'fas fa-tag', color:'color:#40BF27', affix: true, roles: ['user'] }
      },
      {
        path: 'rejected-pin-requests',
        component: () => import('@/views/user/pins/rejected-pin-requests'),
        name: 'Rejected Requests',
        meta: { title: 'Rejected Requests', icon: 'fas fa-tag', color:'color:#CF1F5C', affix: true, roles: ['user'] }
      }
    ]
  },
  {
    path: '/member/payouts',
    component: Layout,
    name: 'Payouts',
    meta: {
      title: 'Payouts',
      icon: 'fas fa-rupee-sign',
      roles: ['user'],
      color:'color:#078F6A'
    },
    hidden: false,
    children: [
      {
        path: 'all',
        component: () => import('@/views/user/payouts/generate'),
        name: 'My Payout',
        meta: { title: 'My Payout', icon: 'far fa-check-circle', color:'color:#DCB527', affix: true, roles: ['user'] }
      },
      // {
      //   path: 'all',
      //   component: () => import('@/views/admin/users/index'),
      //   name: 'PIN Requests',
      //   meta: { title: 'All Payouts', icon: 'fas fa-list', color:'color:#226CBF', affix: true, roles: ['admin'] }
      // }
    ]
  },
  {
    path: '/wallet',
    component: Layout,
    name: 'Wallet',
    meta: {
      title: 'Wallet',
      icon: 'fas fa-wallet',
      roles: ['user'],
      color:'color:#DC7633'
    },
    hidden: false,
    children: [
      {
        path: 'transactions',
        component: () => import('@/views/user/wallet/transactions'),
        name: 'Wallet Transactions',
        meta: { title: 'Wallet Transactions', icon: 'fas fa-wallet', color:'color:#FF5733', affix: true, roles: ['user'] }
      }
    ]
  },
  {
    path: '/reports',
    component: Layout,
    name: 'Reports',
    meta: {
      title: 'Reports',
      icon: 'fas fa-chart-bar',
      roles: ['user'],
      color:'color:#FF5733'
    },
    hidden: false,
    children: [
      {
        path: 'downlines',
        component: () => import('@/views/user/reports/downlines'),
        name: 'Downlines',
        meta: { title: 'Downlines', icon: 'fas fa-user-friends', color:'color:#DC7633', affix: true, roles: ['user'] }
      }
    ]
  },
  {
    path: '/support',
    component: Layout,
    name: 'Support',
    meta: {
      title: 'Support',
      icon: 'fas fa-headset',
      roles: ['user'],
      color:'color:#DC7633'
    },
    hidden: false,
    children: [    
      {
        path: 'my-tickets',
        component: () => import('@/views/user/support/index'),
        name: 'Support Tickets',
        meta: { title: 'Tickets', icon: 'fas fa-envelope-open-text', color:'color:#C39BD3', affix: true, roles: ['user'] }
      }
    ]
  },

  /** when your routing map is too long, you can split it into small modules **/
  //tableRouter,

  // 404 page must be placed at the end !!!
  { path: '*', redirect: '/404', hidden: true }
]

const createRouter = () => new Router({
  // mode: 'history', // require service support
  scrollBehavior: () => ({ y: 0 }),
  base: process.env.MIX_LARAVUE_PATH,
  routes: constantRoutes,
});

const router = createRouter();

// Detail see: https://github.com/vuejs/vue-router/issues/1234#issuecomment-357941465
export function resetRouter() {
  const newRouter = createRouter();
  router.matcher = newRouter.matcher; // reset router
}

export default router;
