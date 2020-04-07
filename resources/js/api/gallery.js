import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/gallery',
    method: 'get',
    params: query
  })
}

export function fetchGallery(id) {
  return request({
    url: '/admin/gallery/'+id,
    method: 'get'
  })
}

export function deleteGallery(id) {
  return request({
    url: '/admin/gallery/'+id+'/delete',
    method: 'delete'
  })
}


export function createGallery(data) {
  return request({
    url: '/admin/gallery',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateGallery(data) {
  return request({
    url: '/admin/gallery/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

