import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/packages',
    method: 'get',
    params: query
  })
}

export function all(query) {
  return request({
    url: '/admin/packages/all',
    method: 'get'
  })
}

export function createPackage(data) {
  return request({
    url: '/admin/packages',
    method: 'post',
    data
  })
}

export function updatePackage(data,id) {
  return request({
    url: '/admin/package/'+id+'/update',
    method: 'post',
    data
  })
}


export function deletePackage(id) {
  return request({
    url: '/admin/package/'+id+'/delete',
    method: 'delete'
  })
}