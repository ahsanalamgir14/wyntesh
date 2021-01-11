import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/color-variants',
    method: 'get',
    params: query
  })
}

export function all(query) {
  return request({
    url: '/admin/color-variants/all',
    method: 'get'
  })
}

export function createColor(data) {
  return request({
    url: '/admin/color-variants',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateColor(data,id) {
  return request({
    url: '/admin/color-variant/'+id+'/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function changeColorStatus(data) {
  return request({
    url: '/admin/color-varient/change-status',
    method: 'post',
    data
  })
}

export function deleteColor(id) {
  return request({
    url: '/admin/color-varient/'+id+'/delete',
    method: 'delete'
  })
}