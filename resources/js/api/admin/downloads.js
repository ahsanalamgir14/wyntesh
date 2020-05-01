import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/downloads',
    method: 'get',
    params: query
  })
}

export function fetchDownload(id) {
  return request({
    url: '/admin/download/'+id,
    method: 'get'
  })
}

export function deleteDownload(id) {
  return request({
    url: '/admin/download/'+id+'/delete',
    method: 'delete'
  })
}


export function createDownload(data) {
  return request({
    url: '/admin/download',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateDownload(data) {
  return request({
    url: '/admin/download/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

