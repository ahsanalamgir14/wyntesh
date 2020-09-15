import request from '@/utils/request'

// User Endpoints

export function getMyBalance() {
  return request({
    url: '/user/wallet/balance',
    method: 'get'
  })
}

export function fetchWithdrawalRequests(query) {
  return request({
    url: '/user/withdrawal-requests',
    method: 'get',
    params: query
  })
}

export function deleteWithdrawalRequest(id) {
  return request({
    url: '/user/withdrawal-requests/'+id+'/delete',
    method: 'delete'
  })
}

export function createWithdrawalRequest(data) {
  return request({
    url: '/user/withdrawal-requests',
    method: 'post',
    data
  })
}


export function fetchWithdrawals(query) {
  return request({
    url: '/user/withdrawals',
    method: 'get',
    params: query
  })
}

export function fetchIncomeWalletTransactions(query) {
  return request({
    url: '/user/income-wallet-transactions',
    method: 'get',
    params: query
  })
}

export function fetchWalletTransfers(query) {
  return request({
    url: '/user/wallet-transfers',
    method: 'get',
    params: query
  })
}

export function fetchMyCreditRequests(query) {
  return request({
    url: '/user/wallet/credit-requests',
    method: 'get',
    params: query
  })
}

export function createTransfer(data) {
  return request({
    url: '/user/wallet/balance/transfer',
    method: 'post',
    data
  })
}

export function createCreditRequest(data) {
  return request({
    url: '/user/wallet/credit-requests',
    method: 'post',
    data
  })
}