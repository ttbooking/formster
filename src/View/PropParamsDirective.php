<?php

declare(strict_types=1);

namespace TTBooking\Formster\View;

class PropParamsDirective
{
    public function __invoke(string $expression): string
    {
        return "<?php if (isset(\$property) && \$property instanceof \TTBooking\Formster\Entities\FinalAuraProperty) {
\$__index = 0;
foreach ($expression as \$__name => \$__default) {
    if (is_int(\$__name)) {
        \$\$__default ??= \TTBooking\Formster\Support\prop_param(\$property, \$__index++, \$__default);
    } else {
        \$\$__name ??= \TTBooking\Formster\Support\prop_param(\$property, \$__index++, \$__name) ?? \$__default;
    }
}

unset(\$__index, \$__name, \$__default); } ?>";
    }
}
