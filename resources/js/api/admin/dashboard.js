import request from '@/utils/request'

export function dashboardStats() {
  return request({
    url: '/admin/stats',
    method: 'get'
  })
}

export function pastOrderStats() {
  return request({
    url: '/admin/order/stats',
    method: 'get'
  })
}

export function pastActivations() {
  return request({
    url: '/admin/activation/stats',
    method: 'get'
  })
}
