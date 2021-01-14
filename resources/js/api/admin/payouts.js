import request from '@/utils/request'

export function fetchPayouts(query) {
  return request({
    url: '/admin/payouts',
    method: 'get',
    params: query
  })
}

export function fetchRewards(query) {
  return request({
    url: '/admin/rewards',
    method: 'get',
    params: query
  })
}

export function addRewrds(data) {
  return request({
    url: '/admin/reward-add',
    method: 'post',
    data
  })
}

export function checkMember(code) {
  return request({
    url: '/admin/check-member/'+code,
    method: 'get'
  })
}


export function generateManualPayout(data) {
  return request({
    url: '/admin/payout/generate',
    method: 'post',
    data
  })
}
export function createHoldingRequest(data) {
  return request({
    url: '/admin/payout/holding',
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
export function getMemberTopWallet(query) {
  return request({
    url: '/admin/top-wallet',
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

export function getMonthlyOverview(query) {
  return request({
    url: '/admin/get-monthly-overview',
    method: 'get',
    params: query
  })
}


export function getGroupAndMatchingPvs(query) {
  return request({
    url: '/admin/group-and-matching-pvs',
    method: 'get',
    params: query
  })
}

export function getMatchingPoints(query) {
  return request({
    url: '/admin/matching-points',
    method: 'get',
    params: query
  })
}

export function generateMatchingPoints(data) {
  return request({
    url: '/admin/payout/generate-matching-points',
    method: 'post',
    data
  })
}