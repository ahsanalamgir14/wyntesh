import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/testimonials',
    method: 'get',
    params: query
  })
}

export function fetchTestimonial(id) {
  return request({
    url: '/admin/testimonial/'+id,
    method: 'get'
  })
}

export function deleteTestimonial(id) {
  return request({
    url: '/admin/testimonial/'+id+'/delete',
    method: 'delete'
  })
}


export function createTestimonial(data) {
  return request({
    url: '/admin/testimonial',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateTestimonial(data) {
  return request({
    url: '/admin/testimonial/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

