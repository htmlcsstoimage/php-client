<?php

declare(strict_types=1);

namespace HtmlCssToImage\Recording;

/** Easing curve used for a scroll movement.
 * @api
 */
enum ScrollEasing: string
{
    case Linear = 'linear';
    case EaseInCubic = 'ease_in_cubic';
    case EaseOutCubic = 'ease_out_cubic';
    case EaseInOutCubic = 'ease_in_out_cubic';
    case EaseInQuad = 'ease_in_quad';
    case EaseOutQuad = 'ease_out_quad';
    case EaseInOutQuad = 'ease_in_out_quad';
    case EaseInQuart = 'ease_in_quart';
    case EaseOutQuart = 'ease_out_quart';
    case EaseInOutQuart = 'ease_in_out_quart';
    case EaseInQuint = 'ease_in_quint';
    case EaseOutQuint = 'ease_out_quint';
    case EaseInOutQuint = 'ease_in_out_quint';
    case EaseInSine = 'ease_in_sine';
    case EaseOutSine = 'ease_out_sine';
    case EaseInOutSine = 'ease_in_out_sine';
    case EaseInExpo = 'ease_in_expo';
    case EaseOutExpo = 'ease_out_expo';
    case EaseInOutExpo = 'ease_in_out_expo';
    case EaseInCirc = 'ease_in_circ';
    case EaseOutCirc = 'ease_out_circ';
    case EaseInOutCirc = 'ease_in_out_circ';
    case EaseInBack = 'ease_in_back';
    case EaseOutBack = 'ease_out_back';
    case EaseInOutBack = 'ease_in_out_back';
    case EaseInElastic = 'ease_in_elastic';
    case EaseOutElastic = 'ease_out_elastic';
    case EaseInOutElastic = 'ease_in_out_elastic';
    case EaseInBounce = 'ease_in_bounce';
    case EaseOutBounce = 'ease_out_bounce';
    case EaseInOutBounce = 'ease_in_out_bounce';
}
