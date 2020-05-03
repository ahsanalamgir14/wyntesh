import request from '@/utils/request'

// User Endpoints

export function fetchPendingPinRequests(query) {
  return request({
    url: '/admin/pending-pin-requests',
    method: 'get',
    params: query
  })
}

export function fetchApprovedPinRequests(query) {
  return request({
    url: '/admin/approved-pin-requests',
    method: 'get',
    params: query
  })
}

export function fetchRejectedPinRequests(query) {
  return request({
    url: '/admin/rejected-pin-requests',
    method: 'get',
    params: query
  })
}

export function fetchRequestPins(query,id) {
  return request({
    url: '/admin/request/'+id+'/pins',
    method: 'get',
    params: query
  })
}

export function fetchAllPins(query) {
  return request({
    url: '/admin/all/pins',
    method: 'get',
    params: query
  })
}

export function deletePinRequest(id) {
  return request({
    url: '/admin/pin-requests/'+id+'/delete',
    method: 'delete'
  })
}

export function rejectPinRequest(data) {
  return request({
    url: '/admin/pin-requests/reject',
    method: 'post',
    data
  })
}

export function generatePin(data) {
  return request({
    url: '/admin/generate-pins',
    method: 'post',
    data
  })
}
