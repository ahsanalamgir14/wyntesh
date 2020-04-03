import request from '@/utils/request'

export function fetchAdminList(query) {
  return request({
    url: '/sadmin/users',
    method: 'get',
    params: query
  })
}


export function createAdminUser(data) {
  return request({
    url: '/sadmin/user',
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

export function updateAdminUser(data) {
  return request({
    url: '/sadmin/user/update',
    method: 'post',
    data
  })
}

export function deleteAdminUser(id) {
  return request({
    url: '/sadmin/user/'+id+'/delete',
    method: 'delete'
  })
}

// Roles
export function fetchRoleList(query) {
  return request({
    url: '/sadmin/roles',
    method: 'get',
    params: query
  })
}


export function createRole(data) {
  return request({
    url: '/sadmin/role',
    method: 'post',
    data
  })
}

export function updateRole(data) {
  return request({
    url: '/sadmin/role/update',
    method: 'post',
    data
  })
}

// Permissions
export function fetchPermissionList(query) {
  return request({
    url: '/sadmin/permissions',
    method: 'get',
    params: query
  })
}


export function createPermission(data) {
  return request({
    url: '/sadmin/permission',
    method: 'post',
    data
  })
}

export function updatePermission(data) {
  return request({
    url: '/sadmin/permission/update',
    method: 'post',
    data
  })
}