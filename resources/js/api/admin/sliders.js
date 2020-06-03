import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/sliders',
    method: 'get',
    params: query
  })
}

export function fetchSlider(id) {
  return request({
    url: '/admin/slider/'+id,
    method: 'get'
  })
}

export function deleteSlider(id) {
  return request({
    url: '/admin/slider/'+id+'/delete',
    method: 'delete'
  })
}


export function createSlider(data) {
  return request({
    url: '/admin/slider',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateSlider(data) {
  return request({
    url: '/admin/slider/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

