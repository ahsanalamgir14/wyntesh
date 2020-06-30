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

export function monthlyJoiningsCount() {
  return request({
    url: '/admin/monthly-joinings',
    method: 'get'
  })
}


export function monthlyBusiness() {
  return request({
    url: '/admin/monthly-business',
    method: 'get'
  })
}
