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
    path: '/invoice/:id',
    component: () => import('@/views/user/shopping/invoice'),
    hidden:true,
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
    path: '/network',
    component: Layout,
    name: 'Network',
    redirect: '/network/members',
    meta: {
      title: 'Network',
      icon: 'fas fa-network-wired',
      roles: ['admin'],
      color:'color:#854CE2'
    },
    children: [
      {
        path: 'members',
        component: () => import('@/views/admin/users/index'),
        name: 'Members',
        meta: { title: 'Members', icon: 'fas fa-user', color:'color:#EE7642', affix: true, roles: ['admin'] }
      },
      {
        path: 'activate',
        component: () => import('@/views/admin/network/activate-accounts'),
        name: 'Activate IDs',
        meta: { title: 'Activate IDs', icon: 'fas fa-check-circle', color:'color:#42f560', affix: true, roles: ['admin'] }
      },
      {
        path: 'add',
        component: () => import('@/views/admin/network/members/add'),
        name: 'Add Members',
        meta: { title: 'Add Members', icon: 'fas fa-plus', color:'color:#EE7642', affix: true, roles: ['admin'] }
      },
      {
        path: 'geneology',
        component: () => import('@/views/admin/network/geneology/index'),
        name: 'Geneology',
        meta: { title: 'Geneology', icon: 'fas fa-sitemap', color:'color:#854CE2', affix: true, roles: ['admin','superadmin'] }
      },
      {
        path: 'geneology/member/:id',
        component: () => import('@/views/admin/network/geneology/member'),
        name: 'Member Geneology',
        hidden:true,
        meta: { title: 'Member Geneology', icon: 'fas fa-sitemap', color:'color:#C39BD3', affix: true, roles: ['admin','superadmin'] }
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
    path: '/products',
    component: Layout,
    name: 'Products',
    redirect: '/products/add',
    meta: {
      title: 'Products',
      icon: 'fas fa-luggage-cart',
      roles: ['admin'],
      color:'color:#078F6A'
    },
    children: [
      {
        path: 'add',
        component: () => import('@/views/admin/products/add'),
        name: 'Add Product',
        meta: { title: 'Add Product', icon: 'fas fa-plus', color:'color:#854CE2', affix: true, roles: ['admin'] }
      },
      {
        path: 'all',
        component: () => import('@/views/admin/products/all-products'),
        name: 'All Products',
        meta: { title: 'All Products', icon: 'fas fa-list', color:'color:#854CE2', affix: true, roles: ['admin'] }
      },
      {
        path: 'edit',
        component: () => import('@/views/admin/products/add'),
        name: 'Edit Product',
        hidden:true, 
        meta: { title: 'Edit Product', icon: 'fas fa-plus', color:'color:#854CE2', affix: true, roles: ['admin'] }
      },
      {
        path: 'categories',
        component: () => import('@/views/admin/products/categories'),
        name: 'Categories',
        meta: { title: 'Categories', icon: 'fas fa-list', color:'color:#35BED1', affix: true, roles: ['admin'] }
      }
    ]
  },
  {
    path: '/shopping',
    component: Layout,
    name: 'Shopping',
    redirect: '/shopping/orders',
    meta: {
      title: 'Shopping',
      icon: 'fas fa-store',
      roles: ['admin'],
      color:'color:#32a852'
    },
    children: [ 
      {
        path: 'orders/new',
        component: () => import('@/views/admin/shopping/orders'),
        name: 'New Orders/all',
        meta: { title: 'New Orders', icon: 'fas fa-fire', color:'color:#fc8803', affix: true, roles: ['admin'] }
      },{
        path: 'orders/all',
        component: () => import('@/views/admin/shopping/all-orders'),
        name: 'All Orders',
        meta: { title: 'All Orders', icon: 'fas fa-truck', color:'color:#35BED1', affix: true, roles: ['admin'] }
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
        name: 'Generate PIN',
        meta: { title: 'Generate PINs', icon: 'fas fa-tag', color:'color:#35BED1', affix: true, roles: ['admin'] }
      },
      {
        path: 'transfer',
        component: () => import('@/views/admin/pins/transfer'),
        name: 'Transfer Pins',
        hidden: false,
        meta: { title: 'Transfer Pins', icon: 'fas fa-exchange-alt', color:'color:#C39BD3', affix: true, roles: ['admin'] }
      },
      {
        path: 'transfer-log',
        component: () => import('@/views/admin/pins/pin-transfer-logs'),
        name: 'Pin Transfer Log',
        meta: { title: 'Pin Transfer Log', icon: 'fas fa-clipboard-list', color:'color:#FF5733', affix: true, roles: ['admin'] }
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
        path: 'withdrawal-requests',
        component: () => import('@/views/admin/wallet/index'),
        name: 'With. Requests',
        meta: { title: 'With. Requests', icon: 'fas fa-wallet', color:'color:#31a816', affix: true, roles: ['admin'] }
      },
      {
        path: 'credit-requests',
        component: () => import('@/views/admin/wallet/credit-requests'),
        name: 'Credit Requests',
        meta: { title: 'Credit Requests', icon: 'fas fa-plus', color:'color:#DCB527', affix: true, roles: ['admin'] }
      },     
      {
        path: 'withdrawals',
        component: () => import('@/views/admin/wallet/withdrawals'),
        name: 'Withdrawals',
        meta: { title: 'Withdrawals', icon: 'fas fa-angle-double-up', color:'color:#4287f5', affix: true, roles: ['admin'] }
      },      
      {
        path: 'transfer-balance',
        component: () => import('@/views/admin/wallet/transfer'),
        name: 'Transfer Balance',
        meta: { title: 'Transfer Balance', icon: 'fas fa-people-arrows', color:'color:#e33b6b', affix: true, roles: ['admin'] }
      },
      {
        path: 'wallet-transactions',
        component: () => import('@/views/admin/wallet/transactions'),
        name: 'Wallet Transactions',
        meta: { title: 'Wallet Transactions', icon: 'fas fa-list-alt', color:'color:#FF5733', affix: true, roles: ['admin'] }
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
        component: () => import('@/views/admin/website/newses/index'),
        name: 'News & Updates',
        meta: { title: 'News', icon: 'fas fa-newspaper', color:'color:#48C9B0', affix: true, roles: ['admin'] }
      },
      {
        path: 'achievers',
        component: () => import('@/views/admin/website/achievers/index'),
        name: 'Achievers',
        meta: { title: 'Achievers', icon: 'fas fa-award', color:'color:#48C9B0', affix: true, roles: ['admin'] }
      },
      {
        path: 'downloads',
        component: () => import('@/views/admin/website/downloads/index'),
        name: 'Downloads',
        meta: { title: 'Downloads', icon: 'fas fa-download', color:'color:#EC7063', affix: true, roles: ['admin'] }
      },
      {
        path: 'gallery',
        component: () => import('@/views/admin/website/gallery/index'),
        name: 'Gallery',
        meta: { title: 'Gallery', icon: 'fas fa-photo-video', color:'color:#A569BD', affix: true, roles: ['admin'] }
      },
      {
        path: 'popups',
        component: () => import('@/views/admin/website/popups/index'),
        name: 'Popups',
        meta: { title: 'Popups', icon: 'fas fa-image', color:'color:#3498DB', affix: true, roles: ['admin'] }
      },
      {
        path: 'sliders',
        component: () => import('@/views/admin/website/sliders/index'),
        name: 'Sliders',
        meta: { title: 'Sliders', icon: 'fas fa-images', color:'color:#16A085', affix: true, roles: ['admin'] }
      },
      {
        path: 'testimonials',
        component: () => import('@/views/admin/website/testimonials/index'),
        name: 'Testimonials',
        meta: { title: 'Testimonials', icon: 'fas fa-user-friends', color:'color:#27AE60', affix: true, roles: ['admin'] }
      },
      {
        path: 'bank-partners',
        component: () => import('@/views/admin/website/bank-partners/index'),
        name: 'Bank Partners',
        meta: { title: 'Bank Partners', icon: 'fas fa-university', color:'color:#196F3D', affix: true, roles: ['admin'] }
      }
    ]
  },
  {
    path: '/config',
    component: Layout,
    name: 'Configrations',
    redirect: '/config/notice',
    meta: {
      title: 'Configrations',
      icon: 'fas fa-cogs',
      roles: ['admin'],
      color:'color:#dd6161'
    },
    hidden: false,
    children: [
      {
        path: 'notice',
        component: () => import('@/views/admin/settings/notices'),
        name: 'Notice',
        meta: { title: 'Notice', icon: 'fas fa-bullhorn', color:'color:#FF5733', affix: true, roles: ['admin'] }
      },
      {
        path: 'welcome-letter',
        component: () => import('@/views/admin/settings/welcome-letter'),
        name: 'Welcome Letter',
        meta: { title: 'Welcome Letter', icon: 'fas fa-handshake', color:'color:#854CE2', affix: true, roles: ['admin'] }
      },
      {
        path: 'emails',
        component: () => import('@/views/admin/settings/emails'),
        name: 'Emails',
        meta: { title: 'Emails', icon: 'fas fa-envelope', color:'color:#fcb103', affix: true, roles: ['admin'] }
      },
      {
        path: 'company-details',
        component: () => import('@/views/admin/settings/company-details'),
        name: 'Company Details',
        meta: { title: 'Company Details', icon: 'fas fa-building', color:'color:#63beff', affix: true, roles: ['admin'] }
      }
    ]
  },
  {
    path: '/marketing',
    component: Layout,
    name: 'Marketing',
    redirect: '/marketing/email-and-message',
    meta: {
      title: 'Marketing',
      icon: 'fas fa-comments-dollar',
      roles: ['admin'],
      color:'color:#db03fc'
    },
    hidden: false,
    children: [
      {
        path: 'email-and-message',
        component: () => import('@/views/admin/marketing/email-and-message'),
        name: 'Send SMS/Email',
        meta: { title: 'Send SMS/Email', icon: 'fas fa-paper-plane', color:'color:#0398fc', affix: true, roles: ['admin'] }
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
    name: 'My Account',
    meta: {
      title: 'My Account',
      icon: 'fas fa-user-circle',
      roles: ['user'],
      color:'color:#DC7633'
    },
    hidden: false,
    children: [
      {
        path: 'profile',
        component: () => import('@/views/user/account/Profile'),
        name: 'Profile & KYC',
        meta: { title: 'Profile & KYC', icon: 'fas fa-user', color:'color:#FF5733', affix: true, roles: ['user'] }
      },
      {
        path: 'welcome-letter',
        component: () => import('@/views/user/account/welcome-letter'),
        name: 'Welcome Letter',
        meta: { title: 'Welcome Letter', icon: 'fas fa-handshake', color:'color:#854CE2', affix: true, roles: ['user'] }
      },
      {
        path: 'activate',
        component: () => import('@/views/user/account/activate-account'),
        name: 'Activate ID',
        meta: { title: 'Activate ID', icon: 'fas fa-check-circle', color:'color:#42f560', affix: true, roles: ['user'] }
      },
      {
        path: 'addresses',
        component: () => import('@/views/user/account/addresses'),
        name: 'Addresses',
        meta: { title: 'Addresses', icon: 'fas fa-map-marked-alt', color:'color:#C39BD3', affix: true, roles: ['user'] }
      },
    ]
  },
  {
    path: '/network',
    component: Layout,
    name: 'Network',
    redirect: '/network/geneology',
    meta: {
      title: 'Network',
      icon: 'fas fa-network-wired',
      roles: ['user'],
      color:'color:#854CE2'
    },
    children: [     
      {
        path: 'add',
        component: () => import('@/views/admin/network/members/add'),
        name: 'Add Member',
        meta: { title: 'Add Member', icon: 'fas fa-plus', color:'color:#EE7642', affix: true, roles: ['user'] }
      },
      {
        path: 'geneology',
        component: () => import('@/views/user/network/my-genealogy'),
        name: 'Geneology',
        meta: { title: 'Geneology', icon: 'fas fa-sitemap', color:'color:#854CE2', affix: true, roles: ['user'] }
      },
      {
        path: 'geneology/member/:id',
        component: () => import('@/views/user/network/member-genealogy'),
        name: 'Member Geneology',
        hidden:true,
        meta: { title: 'Member Geneology', icon: 'fas fa-sitemap', color:'color:#854CE2', affix: true, roles: ['user'] }
      },
      {
        path: 'downlines',
        component: () => import('@/views/user/network/downlines'),
        name: 'Downlines',
        meta: { title: 'Downlines', icon: 'fas fa-user-friends', color:'color:#DC7633', affix: true, roles: ['user'] }
      }
    ]
  },
  {
    path: '/shopping',
    component: Layout,
    name: 'Shopping',
    redirect: '/shopping/products',
    meta: {
      title: 'Shopping',
      icon: 'fas fa-store',
      roles: ['user'],
      color:'color:#32a852'
    },
    children: [     
      {
        path: 'products',
        component: () => import('@/views/user/shopping/products'),
        name: 'Products',
        meta: { title: 'Products', icon: 'fas fa-shopping-basket', color:'color:#EE7642', affix: true, roles: ['user'] }
      },
      {
        path: 'product/:id',
        component: () => import('@/views/user/shopping/product'),
        name: 'Product',
        hidden:true,
        meta: { title: 'Product', icon: 'fas fa-shopping-basket', color:'color:#EE7642', affix: true, roles: ['user'] }
      },
      {
        path: 'cart',
        component: () => import('@/views/user/shopping/cart'),
        name: 'Cart',
        meta: { title: 'Cart', icon: 'fas fa-shopping-cart', color:'color:#854CE2', affix: true, roles: ['user'] }
      },
      {
        path: 'checkout',
        component: () => import('@/views/user/shopping/checkout'),
        hidden:true,
        name: 'Checkout',
        meta: { title: 'Checkout', icon: 'fas fa-shopping-cart', color:'color:#854CE2', affix: true, roles: ['user'] }
      },
      {
        path: 'orders',
        component: () => import('@/views/user/shopping/orders'),
        name: 'Orders',
        meta: { title: 'Orders', icon: 'fas fa-truck', color:'color:#35BED1', affix: true, roles: ['user'] }
      },
      
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
        path: 'pending-pin-requests',
        component: () => import('@/views/user/pins/pending-pin-requests'),
        name: 'PIN Requests',
        meta: { title: 'Create Request', icon: 'fas fa-tag', color:'color:#DC7633', affix: true, roles: ['user'] }
      },
      {
        path: 'all',
        component: () => import('@/views/user/pins/all'),
        name: 'My PINs',
        meta: { title: 'My PINs', icon: 'fas fa-tag', color:'color:#35BED1', affix: true, roles: ['user'] }
      },
      {
        path: 'transfer',
        component: () => import('@/views/user/pins/transfer'),
        name: 'Transfer Pins',
        meta: { title: 'Transfer Pins', icon: 'fas fa-exchange-alt', color:'color:#C39BD3', affix: true, roles: ['user'] }
      },
      {
        path: 'transfer-log',
        component: () => import('@/views/user/pins/pin-transfer-logs'),
        name: 'Pin Transfer Log',
        meta: { title: 'Pin Transfer Log', icon: 'fas fa-clipboard-list', color:'color:#FF5733', affix: true, roles: ['user'] }
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
        path: 'wallet',
        component: () => import('@/views/user/wallet/index'),
        name: 'My Wallet',
        meta: { title: 'My Wallet', icon: 'fas fa-wallet', color:'color:#31a816', affix: true, roles: ['user'] }
      },
      {
        path: 'credit-requests',
        component: () => import('@/views/user/wallet/credit-requests'),
        name: 'Credit Requests',
        meta: { title: 'Credit Requests', icon: 'fas fa-plus', color:'color:#DCB527', affix: true, roles: ['user'] }
      },
      {
        path: 'withdrawals',
        component: () => import('@/views/user/wallet/withdrawals'),
        name: 'Withdrawals',
        meta: { title: 'Withdrawals', icon: 'fas fa-angle-double-up', color:'color:#4287f5', affix: true, roles: ['user'] }
      },      
      {
        path: 'transfer-balance',
        component: () => import('@/views/user/wallet/transfer'),
        name: 'Transfer Balance',
        meta: { title: 'Transfer Balance', icon: 'fas fa-people-arrows', color:'color:#e33b6b', affix: true, roles: ['user'] }
      },
      {
        path: 'wallet-transactions',
        component: () => import('@/views/user/wallet/transactions'),
        name: 'Wallet Transactions',
        meta: { title: 'Wallet Transactions', icon: 'fas fa-list-alt', color:'color:#FF5733', affix: true, roles: ['user'] }
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
