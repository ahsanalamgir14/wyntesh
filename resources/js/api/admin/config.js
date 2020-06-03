import request from '@/utils/request'

export function getStatuesAll(type) {
  if(type){
    return request({
      url: '/admin/statuses/all?type='+type,
      method: 'get'
    })
  }else{
    return request({
      url: '/admin/statuses/all',
      method: 'get'
    })
  }  
}

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

export function getNotificationSettings() {
  return request({
    url: '/admin/notification-settings',
    method: 'get'
  })
}

export function saveNotificationSettings(data) {
  return request({
    url: '/admin/notification-settings',
    method: 'post',
    data
  })
}

export function createNotificationSettings(data) {
  return request({
    url: '/admin/notification-setting/create',
    method: 'post',
    data
  })
}