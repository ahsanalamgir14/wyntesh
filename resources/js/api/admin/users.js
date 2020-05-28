import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/users',
    method: 'get',
    params: query
  })
}


export function createUser(data) {
  return request({
    url: '/admin/user',
    method: 'post',
    data
  })
}

export function changeUserStatus(data) {
  return request({
    url: '/admin/user/change-status',
    method: 'post',
    data
  })
}

export function changeUserActivationStatus(data) {
  return request({
    url: '/admin/user/change-status/activation',
    method: 'post',
    data
  })
}

export function updateExpireDate(data) {
  return request({
    url: '/admin/user/package/update-expire-date',
    method: 'post',
    data
  })
}

export function updateUser(data) {
  return request({
    url: '/admin/user/update',
    method: 'post',
    data
  })
}

export function deleteUser(id) {
  return request({
    url: '/admin/user/'+id+'/delete',
    method: 'delete'
  })
}

