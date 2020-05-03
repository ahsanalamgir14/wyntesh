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

export function createPinRequest(data) {
  return request({
    url: '/user/pin-requests',
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
