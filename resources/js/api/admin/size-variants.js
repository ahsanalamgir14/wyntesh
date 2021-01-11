import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/size-variants',
    method: 'get',
    params: query
  })
}

export function all(query) {
  return request({
    url: '/admin/size-variants/all',
    method: 'get'
  })
}

export function createSize(data) {
  return request({
    url: '/admin/size-variants',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateSize(data,id) {
  return request({
    url: '/admin/size-variant/'+id+'/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function changeSizeStatus(data) {
  return request({
    url: '/admin/size-varient/change-status',
    method: 'post',
    data
  })
}

export function deleteSize(id) {
  return request({
    url: '/admin/size-varient/'+id+'/delete',
    method: 'delete'
  })
}