import request from '@/utils/request'

export function dashboardStats() {
  return request({
    url: '/admin/stats',
    method: 'get'
  })
}
