<?php

namespace App\Http\Controllers;

use App\Enums\AddressType;
use App\Models\Country;
use App\Models\CustomerAddress;
use App\Http\Requests\ProfileCustomRequest;
use App\Http\Requests\PasswordUpdateRequest;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class ProfileCustomController extends Controller
{
    public function profile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var \App\Models\Customer */
        $customer = $user->customer;
        $shippingAddress = $customer->shippingAddress ?: new CustomerAddress(['type'=>AddressType::Shipping]);
        $billingAddress = $customer->billingAddress ?: new CustomerAddress(['type'=>AddressType::Billing]);
        // dd($customer,$shippingAddress->attributesToArray(),$billingAddress,$billingAddress->customer);
        $countries = Country::query()->orderBy('name')->get(); 

        return view('profile_custom.profile-view',compact('customer','user','shippingAddress','billingAddress','countries'));
    }

    public function update(ProfileCustomRequest $request)
    {
        $customerData = $request->validated();
        $shippingData = $customerData['shipping'];
        $billingData = $customerData['billing'];

        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var \App\Models\Customer $customer */
        $customer = $user->customer;

        $customer->update($customerData);

        /** shipping address condition */
        if ($customer->shippingAddress) {
            $customer->shippingAddress->update($shippingData);
        }else{
            $shippingData['type'] = AddressType::Shipping->value;
            $shippingData['customer_id'] = $customer->user_id;
            CustomerAddress::create($shippingData);
        }

        /** billing address condition */
        if ($customer->billingAddress) {
            $customer->billingAddress->update($billingData);
        } else {
            $billingData['type'] = AddressType::Billing->value;
            $billingData['customer_id'] = $customer->user_id;
            CustomerAddress::create($billingData);
        }

        $flash = 'flash';
        $request->session()->$flash('flash_message','Profile was successfully updated');

        return redirect()->route('profile-information.profile');
    }

    public function passwordUpdate(PasswordUpdateRequest $request)
    {
        /** @var \App\Models\User */
        $user = $request->user();

        $passwordData = $request->validated();

        $user->password = Hash::make($passwordData['new_password']);
        $user->save();

        $flash = 'flash';
        $request->session()->$flash('flash_message','Your password was successfully updated');

        return redirect()->route('profile-information.profile');
    }
}
