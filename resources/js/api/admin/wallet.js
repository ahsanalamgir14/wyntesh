import request from '@/utils/request'

// User Endpoints

export function fetchWithdrawalRequests(query) {
  return request({
    url: '/admin/withdrawal-requests',
    method: 'get',
    params: query
  })
}

export function deleteWithdrawalRequest(id) {
  return request({
    url: '/admin/withdrawal-requests/'+id+'/delete',
    method: 'delete'
  })
}

export function rejectWithdrawalRequest(data) {
  return request({
    url: '/admin/withdrawal-requests/reject',
    method: 'post',
    data
  })
}

export function approveWithdrawalRequest(data) {
  return request({
    url: '/admin/withdrawal-requests/approve',
    method: 'post',
    data
  })
}