import request from '@/utils/request'

// User Endpoints

export function getPackages() {
  return request({
    url: '/user/packages/all',
    method: 'get'
  })
}

export function getCurrencies() {
  return request({
    url: '/user/currencies/all',
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

export function getStatuesAll(type) {
  if(type){
    return request({
      url: '/user/statuses/all?type='+type,
      method: 'get'
    })
  }else{
    return request({
      url: '/user/statuses/all',
      method: 'get'
    })
  }  
}

export function getAllStates() {
  return request({
    url: '/states',
    method: 'get'
  })
}
export function getStateCities(state) {
  return request({
    url: '/cities/'+state,
    method: 'get'
  })
}

export function getAllCountry() {
  return request({
    url: '/country',
    method: 'get'
  })
}

export function getCountryStates(country) {
  return request({
    url: '/states/'+country,
    method: 'get'
  })
}
