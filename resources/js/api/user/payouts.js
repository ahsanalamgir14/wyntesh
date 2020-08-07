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

export function myAffiliateBonus(query) {
  return request({
    url: '/user/affiliate-bonus',
    method: 'get',
    params: query
  })
}

export function getIncomeHoldings(query) {
  return request({
    url: '/user/income-holdings',
    method: 'get',
    params: query
  })
}

export function getIncomeHoldingPayouts() {
  return request({
    url: '/user/income-holding-payouts',
    method: 'get'
  })
}

export function getGroupAndMatchingPvs(query) {
  return request({
    url: '/user/group-and-matching-pvs',
    method: 'get',
    params: query
  })
}



export function getRankLogs(query) {
  return request({
    url: '/user/rank-logs',
    method: 'get',
    params: query
  })
}