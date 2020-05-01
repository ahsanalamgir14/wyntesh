import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/newses',
    method: 'get',
    params: query
  })
}

export function fetchNews(id) {
  return request({
    url: '/admin/news/'+id,
    method: 'get'
  })
}

export function deleteNews(id) {
  return request({
    url: '/admin/news/'+id+'/delete',
    method: 'delete'
  })
}


export function createNews(data) {
  return request({
    url: '/admin/news',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateNews(data) {
  return request({
    url: '/admin/news/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

