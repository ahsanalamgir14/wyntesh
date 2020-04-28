import request from '@/utils/request'


export function getProfile() {
  return request({
    url: '/user/profile',
    method: 'get'
  })
}

export function updateProfile(data) {
  return request({
    url: '/user/profile',
    method: 'post',
    data
  })
}

export function registerMember(data) {
  return request({
    url: '/member/registration',
    method: 'post',
    data
  })
}

export function checkSponsorCode(code) {
  return request({
    url: '/member/check-sponsor-code/'+code,
    method: 'get'
  })
}

export function getAdminGeneology() {
  return request({
    url: '/admin/geneology',
    method: 'get'
  })
}