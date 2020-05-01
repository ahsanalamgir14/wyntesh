import request from '@/utils/request'

export function fetchAdminList(query) {
  return request({
    url: '/superadmin/users',
    method: 'get',
    params: query
  })
}


export function createAdminUser(data) {
  return request({
    url: '/superadmin/user',
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

export function changeAdminUserStatus(data) {
  return request({
    url: '/superadmin/user/change-status',
    method: 'post',
    data
  })
}

export function updateAdminUser(data) {
  return request({
    url: '/superadmin/user/update',
    method: 'post',
    data
  })
}

export function deleteAdminUser(id) {
  return request({
    url: '/superadmin/user/'+id+'/delete',
    method: 'delete'
  })
}

// Roles
export function fetchRoleList(query) {
  return request({
    url: '/superadmin/roles',
    method: 'get',
    params: query
  })
}


export function createRole(data) {
  return request({
    url: '/superadmin/role',
    method: 'post',
    data
  })
}

export function updateRole(data) {
  return request({
    url: '/superadmin/role/update',
    method: 'post',
    data
  })
}

// Permissions
export function fetchPermissionList(query) {
  return request({
    url: '/superadmin/permissions',
    method: 'get',
    params: query
  })
}


export function createPermission(data) {
  return request({
    url: '/superadmin/permission',
    method: 'post',
    data
  })
}

export function updatePermission(data) {
  return request({
    url: '/superadmin/permission/update',
    method: 'post',
    data
  })
}