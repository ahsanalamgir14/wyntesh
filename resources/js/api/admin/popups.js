import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/popups',
    method: 'get',
    params: query
  })
}

export function fetchPopup(id) {
  return request({
    url: '/admin/popup/'+id,
    method: 'get'
  })
}

export function deletePopup(id) {
  return request({
    url: '/admin/popup/'+id+'/delete',
    method: 'delete'
  })
}


export function createPopup(data) {
  return request({
    url: '/admin/popup',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updatePopup(data) {
  return request({
    url: '/admin/popup/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

