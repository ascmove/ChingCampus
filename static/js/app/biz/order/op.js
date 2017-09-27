define(['core', 'tpl'], function(core, tpl) {
	var modal = {};
	modal.init = function(fromDetail) {
		if (typeof fromDetail === undefined) {
			fromDetail = true
		}
		modal.fromDetail = fromDetail;
		$('.order-cancel select').unbind('change').change(function() {
			var orderid = $(this).data('orderid');
			var val = $(this).val();
			if (val == '') {
				return
			}
			FoxUI.confirm('确认要取消该订单吗?', '提示', function() {
				modal.cancel(orderid, val, true)
			})
		});
        $('.order-more select').unbind('change').change(function() {
            var orderid = $(this).data('orderid');
            var val = $(this).val();
            switch(val)
            {
                case '':
                    return;
                    break;
                case '申请退单':
                    FoxUI.confirm('申请取消订单需要服务者同意，建议您协商后操作，继续操作？', '提示', function() {
                        core.json('order/op/interrupt', {
                            s: orderid,
                        }, function(json) {
                            if (json.status == 1) {
                                FoxUI.toast.show(json.result.message);
                                window.location.reload();
                            }else{
                                FoxUI.alert(json.result.message,'提示');
                            }
                        }, true, true)
                    })
                    return;
                    break;
                default:
                    FoxUI.confirm('建议您协商后操作，继续操作？', '提示', function() {
                        core.json('order/op/complain', {
                            s: orderid,
                            p: val
                        }, function(json) {
                            if (json.status == 1) {
                                FoxUI.toast.show(json.result.message);
                                window.location.reload();
                            }else{
                                FoxUI.alert(json.result.message,'提示');
                            }
                        }, true, true)
                    })
                    return;
            }
        });
		$('.cancel-interrupt').unbind('click').click(function() {
		    var orderid = $(this).data('orderid');
            FoxUI.confirm('取消申诉订单状态将变为待确认，确认吗?', '提示', function() {
                core.json('order/op/cancel_interrupt', {
                    s: orderid
                }, function(json) {
                    if (json.status == 1) {
                        FoxUI.toast.show(json.result.message);
                        location.reload();
                    }else{
                        FoxUI.alert(json.result.message,'提示');
                    }
                }, true, true)
            })
		});
		$('.evaluate').unbind('click').click(function() {
			var orderid = $(this).data('orderid');
            window.location.href = core.getUrl('order/evaluate',{'s':orderid});
		});
		$('.order-delete').unbind('click').click(function() {
			var orderid = $(this).data('orderid');
			FoxUI.confirm('确认要删除该订单吗?', '提示', function() {
				modal.delete(orderid, 1)
			})
		});
		$('.additional').unbind('click').click(function() {
            var orderid = $(this).data('orderid');
            FoxUI.dialog({
                title: '追加付款',
                text: '<span style="font-size: 12px;text-align: center">追加付款平台不收取手续费</span>',
                extraClass: 'fui-dialog-confirm',
                prompt: {
                    placeholder: '最低追加1元'
                },
                buttons: [{
                    text: '取消',
                    onclick:''
                },{
                    text: '确认',
                    onclick: function (container,value) {
                        if(isNaN(parseInt(value))){
                            FoxUI.alert('请输入正确数值!','提示');
                            return;
                        }else{
                            core.json('order/pay/extra', {
                                s: orderid,
                                y: parseInt(value)
                            }, function(pay_json) {
                                if (pay_json.status == 1) {
                                    window.location.href = pay_json.result.url;
                                }else{
                                    FoxUI.alert(pay_json.result.message,'提示');
                                }
                            }, true, true)
                        }
                    }
                },]
            });
		});
		$('.service-confirm').unbind('click').click(function() {
            var orderid = $(this).data('orderid');
            FoxUI.confirm('确认完成将立即付款给服务者，继续操作吗？', '提示', function() {
				core.json('order/op/confirm', {
					s: orderid
				}, function(json) {
					if (json.status == 1) {
                        FoxUI.toast.show(json.result.message);
                        location.reload();
					}else{
						FoxUI.alert(json.result.message,'提示');
					}
				}, true, true)
            })
		});




		//服务者操作选项
        $('.service-start').unbind('click').click(function() {
            var orderid = $(this).data('orderid');
            core.json('order/op/service_start', {
                s: orderid
            }, function(json) {
                if (json.status == 1) {
                    FoxUI.toast.show(json.result.message);
                    location.reload();
                }else{
                    FoxUI.alert(json.result.message,'提示');
                }
            }, true, true)
        });
        $('.service-not-ok').unbind('click').click(function() {
            var orderid = $(this).data('orderid');
            core.json('order/op/service_cancel', {
                s: orderid
            }, function(json) {
                if (json.status == 1) {
                    FoxUI.toast.show(json.result.message);
                    location.reload();
                }else{
                    FoxUI.alert(json.result.message,'提示');
                }
            }, true, true)
        });
        $('.service-finish').unbind('click').click(function() {
            var orderid = $(this).data('orderid');
            FoxUI.confirm('服务是否按照要求完成？', '提示', function() {
                core.json('order/op/service_finish', {
                    s: orderid
                }, function(json) {
                    if (json.status == 1) {
                        FoxUI.toast.show(json.result.message);
                        location.reload();
                    }else{
                        FoxUI.alert(json.result.message,'提示');
                    }
                }, true, true)
            })
        });
        $('.service-complain-done').unbind('click').click(function() {
            var orderid = $(this).data('orderid');
            FoxUI.confirm('同意退单将立即退款给对方，继续操作吗？', '提示', function() {
                core.json('order/op/service_complain_done', {
                    s: orderid
                }, function(json) {
                    if (json.status == 1) {
                        FoxUI.toast.show(json.result.message);
                        window.location.reload();
                    }else{
                        FoxUI.alert(json.result.message,'提示');
                    }
                }, true, true)
            })
        });
        $('.service-complain-fail').unbind('click').click(function() {
            var orderid = $(this).data('orderid');
            FoxUI.confirm('拒绝退单状态将变为待确认，继续操作吗？', '提示', function() {
                core.json('order/op/service_complain_fail', {
                    s: orderid
                }, function(json) {
                    if (json.status == 1) {
                        FoxUI.toast.show(json.result.message);
                        window.location.reload();
                    }else{
                        FoxUI.alert(json.result.message,'提示');
                    }
                }, true, true)
            })
        });
        $('.qiang').unbind('click').click(function() {
            var orderid = $(this).data('orderid');
            FoxUI.toast.show('拼命抢单中...');
            core.json('order/op/take_order', {
                s: orderid
            }, function(json) {
                if (json.status == 1) {
                    FoxUI.toast.show(json.result.message);
                    window.location.href = core.getUrl('order/detail','&outTradeNo='+orderid);
                }else{
                    FoxUI.alert(json.result.message,'提示');
                }
            }, true, true)
        });
        $('.hall-more select').unbind('change').change(function() {
            var orderid = $(this).data('orderid');
            var openid = $(this).data('openid');
            var servicetypeid = $(this).data('service-id');
            var val = $(this).val();
            switch(val)
            {
                case '':
                    return;
                    break;
                case '不再接收Ta的订单':
                    FoxUI.confirm('屏蔽对方后目前无法恢复，继续操作？', '提示', function() {
                        core.json('order/op/user_black_list', {
                            s: orderid,
                            u: openid
                        }, function(json) {
                            if (json.status == 1) {
                                FoxUI.toast.show(json.result.message);
                                location.reload();
                            }else{
                                FoxUI.alert(json.result.message,'提示');
                            }
                        }, true, true)
                    })
                    return;
                    break;
                case '不再接收此类订单':
                    FoxUI.confirm('关闭后您可以在个人中心重新开启，继续操作？', '提示', function() {
                        core.json('member/update_notice', {
                            s: 'cluser',
                            h: servicetypeid,
                            u: 0
                        }, function(json) {
                            if (json.status == 1) {
                                FoxUI.toast.show(json.result.message);
                                return;
                            }else{
                                FoxUI.alert(json.result.message,'提示');
                            }
                        }, true, true)
                    })
                    return;
                    break;
                default:
                    return;
            }
        });
        $('.service-more select').unbind('change').change(function() {
            var orderid = $(this).data('orderid');
            var openid = $(this).data('openid');
            var servicetypeid = $(this).data('service-id');
            var val = $(this).val();
            switch(val)
            {
                case '':
                    return;
                    break;
                case '服务者投诉：恶意退款':
                    FoxUI.confirm('此操作需要后台人员审核，继续操作？', '提示', function() {
                        core.json('order/op/service_complain', {
                            s: orderid,
                            p: val
                        }, function(json) {
                            if (json.status == 1) {
                                FoxUI.toast.show(json.result.message);
                                location.reload();
                            }else{
                                FoxUI.alert(json.result.message,'提示');
                            }
                        }, true, true)
                    })
                    return;
                    break;
                case '服务者投诉：订单拖延':
                    FoxUI.confirm('此操作需要后台人员审核，继续操作？', '提示', function() {
                        core.json('order/op/service_complain', {
                            s: orderid,
                            p: val
                        }, function(json) {
                            if (json.status == 1) {
                                FoxUI.toast.show(json.result.message);
                                location.reload();
                            }else{
                                FoxUI.alert(json.result.message,'提示');
                            }
                        }, true, true)
                    })
                    return;
                    break;
                case '服务者投诉：其他原因':
                    FoxUI.confirm('此操作需要后台人员审核，继续操作？', '提示', function() {
                        core.json('order/op/service_complain', {
                            s: orderid,
                            p: val
                        }, function(json) {
                            if (json.status == 1) {
                                FoxUI.toast.show(json.result.message);
                                location.reload();
                            }else{
                                FoxUI.alert(json.result.message,'提示');
                            }
                        }, true, true)
                    })
                    return;
                    break;
                default:
                    return;
            }
        });






        $('.order-recover').unbind('click').click(function() {
			var orderid = $(this).data('orderid');
			FoxUI.confirm('确认要恢复该订单吗?', '提示', function() {
				modal.delete(orderid, 0)
			})
		});
		$('.order-finish').unbind('click').click(function() {
			var orderid = $(this).data('orderid');
			FoxUI.confirm('确认已收到货了吗?', '提示', function() {
				modal.finish(orderid)
			})
		});
		$('.order-verify').unbind('click').click(function() {
			var orderid = $(this).data('orderid');
			modal.verify(orderid)
		})
	};
	modal.cancel = function(id, remark) {
		core.json('order/op/cancel', {
			id: id,
			remark: remark
		}, function(pay_json) {
			if (pay_json.status == 1) {
                FoxUI.toast.show(pay_json.result.message);
				if (modal.fromDetail) {
					location.href = core.getUrl('order')
				} else {
					$(".order-item[data-orderid='" + id + "']").remove()
				}
				return
			}
			FoxUI.toast.show(pay_json.result.message)
		}, true, true)
	};
	modal.delete = function(id, userdeleted) {
		core.json('order/op/delete', {
			id: id,
			userdeleted: userdeleted
		}, function(pay_json) {
			if (pay_json.status == 1) {
                FoxUI.toast.show(pay_json.result.message);
				if (modal.fromDetail) {
					location.href = core.getUrl('order')
				} else {
					$(".order-item[data-orderid='" + id + "']").remove()
				}
				return
			}
			FoxUI.toast.show(pay_json.result)
		}, true, true)
	};
	modal.finish = function(id) {
		core.json('order/op/finish', {
			id: id
		}, function(pay_json) {
			if (pay_json.status == 1) {
				location.reload();
				return
			}
			FoxUI.toast.show(pay_json.result)
		}, true, true)
	};
	modal.verify = function(orderid) {
		container = new FoxUIModal({
			content: $(".order-verify-hidden").html(),
			extraClass: "popup-modal",
			maskClick: function() {
				container.close()
			}
		});
		container.show();
		$('.verify-pop').find('.close').unbind('click').click(function() {
			container.close()
		});
		core.json('verify/qrcode', {
			id: orderid
		}, function(ret) {
			if (ret.status == 0) {
				FoxUI.alert('生成出错，请刷新重试!','提示');
				return
			}
			var time = +new Date();
			$('.verify-pop').find('.qrimg').attr('src', ret.result.url + "?timestamp=" + time).show()
		}, false, true)
	};
	return modal
});