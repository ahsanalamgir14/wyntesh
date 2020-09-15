import request from '@/utils/request'

// User Endpoints

export function fetchTransferRequests(query) {
  return request({
    url: '/admin/transfer-requests',
    method: 'get',
    params: query
  })
}

export function deleteTransferRequest(id) {
  return request({
    url: '/admin/transfer-requests/'+id+'/delete',
    method: 'delete'
  })
}

export function rejectTransferRequest(data) {
  return request({
    url: '/admin/transfer-requests/reject',
    method: 'post',
    data
  })
}

export function approveTransferRequest(data) {
  return request({
    url: '/admin/transfer-requests/approve',
    method: 'post',
    data
  })
}

export function fetchWithdrawals(query) {
  return request({
    url: '/admin/withdrawals',
    method: 'get',
    params: query
  })
}

export function fetchWalletTransactions(query) {
  return request({
    url: '/admin/wallet-transactions',
    method: 'get',
    params: query
  })
}
export function fetchIncomeWalletTransactions(query) {
  return request({
    url: '/admin/income-wallet-transactions',
    method: 'get',
    params: query
  })
}

export function fetchWalletTransfers(query) {
  return request({
    url: '/admin/wallet-transfers',
    method: 'get',
    params: query
  })
}

export function createTransfer(data) {
  return request({
    url: '/admin/wallet/balance/transfer',
    method: 'post',
    data
  })
}

export function addBalance(data) {
  return request({
    url: '/admin/wallet/balance/add',
    method: 'post',
    data
  })
}

export function fetchCreditRequests(query) {
  return request({
    url: '/admin/wallet/credit-requests',
    method: 'get',
    params: query
  })
}

export function approveCreditRequest(data) {
  return request({
    url: '/admin/wallet/approve-credit-requests',
    method: 'post',
    data
  })
}

export function rejectCreditRequest(data) {
  return request({
    url: '/admin/wallet/reject-credit-requests',
    method: 'post',
    data
  })
}

export function getDebitTransactions(query) {
  return request({
    url: '/admin/wallet/debits',
    method: 'get',
    params: query
  })
}

export function debitBalance(data) {
  return request({
    url: '/admin/wallet/balance/debit',
    method: 'post',
    data
  })
}
