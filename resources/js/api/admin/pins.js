import request from '@/utils/request'

// User Endpoints

export function fetchAllPinRequests(query) {
  return request({
    url: '/admin/all-pin-requests',
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
