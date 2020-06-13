import request from '@/utils/request'

export function getAllIncomes(id) {
  return request({
    url: '/user/incomes/all',
    method: 'get'
  })
}


export function fetchPayouts(query) {
  return request({
    url: '/user/payouts',
    method: 'get',
    params: query
  })
}

export function getPayoutIncomes(query) {
  return request({
    url: '/user/payout-incomes',
    method: 'get',
    params: query
  })
}
