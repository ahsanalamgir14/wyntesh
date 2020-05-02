import request from '@/utils/request'

// User Endpoints

export function getPackages() {
  return request({
    url: '/user/packages/all',
    method: 'get'
  })
}

export function getPaymentModes() {
  return request({
    url: '/user/payment-modes/all',
    method: 'get'
  })
}

export function getTransactionTypes() {
  return request({
    url: '/user/transaction-types/all',
    method: 'get'
  })
}

export function getBankPartners() {
  return request({
    url: '/user/bank-partners/all',
    method: 'get'
  })
}
