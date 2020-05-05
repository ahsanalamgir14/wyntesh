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

export function createWithdrawalRequest(data) {
  return request({
    url: '/admin/withdrawal-requests',
    method: 'post',
    data
  })
}