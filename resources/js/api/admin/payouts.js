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

export function getPayoutIncomes(query) {
  return request({
    url: '/admin/payout-incomes',
    method: 'get',
    params: query
  })
}

export function getMemberPayouts(query) {
  return request({
    url: '/admin/payouts/member',
    method: 'get',
    params: query
  })
}

export function getMemberTDS(query) {
  return request({
    url: '/admin/tds/member',
    method: 'get',
    params: query
  })
}

export function getMemberPayoutIncomes(query) {
  return request({
    url: '/admin/payout-incomes/member',
    method: 'get',
    params: query
  })
}

export function getMemberIncomeHoldings(query) {
  return request({
    url: '/admin/income-holdings/member',
    method: 'get',
    params: query
  })
}

export function releaseMemberHoldPayout(data) {
  return request({
    url: '/admin/payout/release/member-holding',
    method: 'post',
    data
  })
}

export function getGroupAndMatchingPvs(query) {
  return request({
    url: '/admin/group-and-matching-pvs',
    method: 'get',
    params: query
  })
}