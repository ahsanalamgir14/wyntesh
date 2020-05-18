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

export function addMember(data) {
  return request({
    url: '/member/add',
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

export function checkMemberCode(code) {
  return request({
    url: '/member/check-member-code/'+code,
    method: 'get'
  })
}

export function getAdminGeneology() {
  return request({
    url: '/admin/geneology',
    method: 'get'
  })
}

export function getAdminMemberGeneology(id) {
  return request({
    url: '/admin/geneology/member/'+id,
    method: 'get'
  })
}

export function getMyGeneology() {
  return request({
    url: '/user/geneology',
    method: 'get'
  })
}

export function getMyMemberGeneology(id) {
  return request({
    url: '/user/geneology/member/'+id,
    method: 'get'
  })
}

export function getDownlines(query) {
  return request({
    url: '/user/downlines',
    method: 'get',
    params: query
  })
}