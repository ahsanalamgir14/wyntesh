import request from '@/utils/request'

export function dashboardStats() {
  return request({
    url: '/user/stats',
    method: 'get'
  })
}

export function orderStats() {
  return request({
    url: '/user/order/stats',
    method: 'get'
  })
}

export function payoutStats() {
  return request({
    url: '/user/payout/stats',
    method: 'get'
  })
}

export function downlineStats() {
  return request({
    url: '/user/downline/stats',
    method: 'get'
  })
}

export function latestDownlines() {
  return request({
    url: '/user/downline/latest',
    method: 'get'
  })
}

export function latestTransactions() {
  return request({
    url: '/user/transactions/latest',
    method: 'get'
  })
}
