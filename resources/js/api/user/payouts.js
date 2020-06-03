import request from '@/utils/request'

export function fetchPayouts(query) {
  return request({
    url: '/user/payouts',
    method: 'get',
    params: query
  })
}
