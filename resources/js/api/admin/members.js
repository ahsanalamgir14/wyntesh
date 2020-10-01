import request from '@/utils/request'

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

export function getMemberBalance(code) {
  return request({
    url: '/admin/member/balance/'+code,
    method: 'get'
  })
}
export function getMemberIncomeBalance(code) {
  return request({
    url: '/admin/member/income-balance/'+code,
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
