import { Router } from 'express';
import { body, param } from 'express-validator';
import type { AuthRequest } from '../middleware/auth';
import { authenticate } from '../middleware/auth';
import { validate } from '../middleware/errorHandler';
import * as addressService from '../services/address.service';
import { successResponse } from '../utils/response';

const router = Router();

router.use(authenticate);

router.get('/', async (req: AuthRequest, res, next) => {
  try {
    const addresses = await addressService.listAddresses(req.user!.id);
    res.json(successResponse('Addresses retrieved', addresses));
  } catch (err) {
    next(err);
  }
});

router.get(
  '/:id',
  validate([param('id').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const address = await addressService.getAddress(req.user!.id, Number(req.params.id));
      res.json(successResponse('Address retrieved', address));
    } catch (err) {
      next(err);
    }
  },
);

const addressBodyValidators = [
  body('recipientName').isString().trim().notEmpty(),
  body('mobile').isString().trim().notEmpty(),
  body('email').optional({ nullable: true, checkFalsy: true }).isEmail(),
  body('flatHouseNo').isString().trim().notEmpty(),
  body('apartmentStreetLocality').isString().trim().notEmpty(),
  body('pincode').isString().trim().notEmpty(),
  body('addressType').optional().isIn(['Home', 'Work', 'Other']),
  body('isDefault').optional().isBoolean(),
];

router.post('/', validate(addressBodyValidators), async (req: AuthRequest, res, next) => {
  try {
    const address = await addressService.createAddress(req.user!.id, {
      recipientName: req.body.recipientName,
      mobile: req.body.mobile,
      email: req.body.email,
      flatHouseNo: req.body.flatHouseNo,
      apartmentStreetLocality: req.body.apartmentStreetLocality,
      pincode: req.body.pincode,
      addressType: req.body.addressType,
      isDefault: req.body.isDefault,
    });
    res.status(201).json(successResponse('Address saved', address));
  } catch (err) {
    next(err);
  }
});

router.patch(
  '/:id',
  validate([param('id').isInt({ min: 1 }), ...addressBodyValidators]),
  async (req: AuthRequest, res, next) => {
    try {
      const address = await addressService.updateAddress(req.user!.id, Number(req.params.id), {
        recipientName: req.body.recipientName,
        mobile: req.body.mobile,
        email: req.body.email,
        flatHouseNo: req.body.flatHouseNo,
        apartmentStreetLocality: req.body.apartmentStreetLocality,
        pincode: req.body.pincode,
        addressType: req.body.addressType,
        isDefault: req.body.isDefault,
      });
      res.json(successResponse('Address updated', address));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/:id/default',
  validate([param('id').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const address = await addressService.setDefaultAddress(req.user!.id, Number(req.params.id));
      res.json(successResponse('Default address updated', address));
    } catch (err) {
      next(err);
    }
  },
);

router.delete(
  '/:id',
  validate([param('id').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const result = await addressService.deleteAddress(req.user!.id, Number(req.params.id));
      res.json(successResponse('Address deleted', result));
    } catch (err) {
      next(err);
    }
  },
);

export default router;
