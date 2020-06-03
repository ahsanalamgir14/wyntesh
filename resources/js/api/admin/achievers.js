import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/achievers',
    method: 'get',
    params: query
  })
}

export function fetchAchiever(id) {
  return request({
    url: '/admin/achiever/'+id,
    method: 'get'
  })
}

export function deleteAchiever(id) {
  return request({
    url: '/admin/achiever/'+id+'/delete',
    method: 'delete'
  })
}


export function createAchiever(data) {
  return request({
    url: '/admin/achiever',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateAchiever(data) {
  return request({
    url: '/admin/achiever/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

