import request from '@/utils/request'

// User Endpoints

export function fetchMyPendingPinRequests(query) {
  return request({
    url: '/user/pending-pin-requests',
    method: 'get',
    params: query
  })
}

export function fetchMyApprovedPinRequests(query) {
  return request({
    url: '/user/approved-pin-requests',
    method: 'get',
    params: query
  })
}

export function fetchMyRejectedPinRequests(query) {
  return request({
    url: '/user/rejected-pin-requests',
    method: 'get',
    params: query
  })
}

export function fetchRequestPins(query,id) {
  return request({
    url: '/user/request/'+id+'/pins',
    method: 'get',
    params: query
  })
}



export function fetchMyPins(query) {
  return request({
    url: '/user/my/pins',
    method: 'get',
    params: query
  })
}

export function fetchNotUsedPins(query) {
  return request({
    url: '/user/unused/my/pins',
    method: 'get',
    params: query
  })
}

export function fetchPinTransferLog(query) {
  return request({
    url: '/user/pins/transfer-log',
    method: 'get',
    params: query
  })
}

export function getMyUsedPins(query) {
  return request({
    url: '/user/account/pins/used',
    method: 'get',
    params: query
  })
}

export function checkPin(pin) {
  return request({
    url: '/user/pin/check/'+pin,
    method: 'get'
  })
}

export function createPinRequest(data) {
  return request({
    url: '/user/pin-requests',
    method: 'post',
    data
  })
}

export function transferPinsToMember(data) {
  return request({
    url: '/user/pins/transfer',
    method: 'post',
    data
  })
}

export function deletePinRequest(id) {
  return request({
    url: '/user/pin-requests/'+id+'/delete',
    method: 'delete'
  })
}

export function activatePinAccount(data) {
  return request({
    url: '/user/account/pin/activation',
    method: 'post',
    data
  })
}
