import request from '@/utils/request'

export function dashboardStats() {
  return request({
    url: '/admin/stats',
    method: 'get'
  })
}

export function getSettings() {
  return request({
    url: '/sadmin/settings',
    method: 'get'
  })
}

export function saveSettings(data) {
  return request({
    url: '/sadmin/settings',
    method: 'post',
    data
  })
}

export function franchiseDashboardStats() {
  return request({
    url: '/admin/stats/franchise',
    method: 'get'
  })
}
