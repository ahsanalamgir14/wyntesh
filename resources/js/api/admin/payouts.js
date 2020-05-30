import request from '@/utils/request'

export function fetchPayouts(query) {
  return request({
    url: '/admin/payouts',
    method: 'get',
    params: query
  })
}

export function generateManualPayout(data) {
  return request({
    url: '/admin/payout/generate',
    method: 'post',
    data
  })
}
