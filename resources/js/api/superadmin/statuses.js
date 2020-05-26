import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/superadmin/statuses',
    method: 'get',
    params: query
  })
}

export function fetchStatus(id) {
  return request({
    url: '/superadmin/status/'+id,
    method: 'get'
  })
}

export function deleteStatus(id) {
  return request({
    url: '/superadmin/status/'+id+'/delete',
    method: 'delete'
  })
}


export function createStatus(data) {
  return request({
    url: '/superadmin/status',
    method: 'post',
    data
  })
}

export function updateStatus(data) {
  return request({
    url: '/superadmin/status/update',
    method: 'post',
    data
  })
}

